<?php

namespace App\Http\Controllers;

use App\Models\MetaServer;
use App\Models\Slave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;


class SlaveController extends Controller
{
    /**
     * List all slaves or filter by master_id.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'master_id' => 'sometimes|integer|exists:tb_masters,id',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',  // Validate page parameter
            'search' => 'sometimes|string|max:255',
        ]);

        // Retrieve query parameters
        $master_id = $request->query('master_id');
        $per_page = $request->query('per_page', 15);
        $search = $request->query('search');

        // Build the query
        $query = Slave::with(['master', 'server', 'mappedByUser']); // Remove 'sl_mt5_id' from 'with'

        // Apply filtering if master_id is provided
        if ($master_id) {
            $query->where('master_id', $master_id);
        }

        // Apply search filter for sl_mt5_id if provided
        if ($search) {
            $query->where('sl_mt5_id', 'LIKE', "%{$search}%");
        }

        // Execute the query with pagination (page is handled automatically)
        $slaves = $query->paginate($per_page);

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $slaves,
        ], 200);
    }


    public function validateMT5Ids(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'mt5_ids' => 'required|string', // Expect mt5_ids as a comma-separated string
        ]);

        // Step 1: Extract and deduplicate IDs
        $mt5_ids = explode(',', $validated['mt5_ids']);
        $mt5_ids = array_map('trim', $mt5_ids); // Remove any extra spaces

        // Check for duplicates in the input
        $duplicates = array_unique(array_diff_assoc($mt5_ids, array_unique($mt5_ids)));

        if (!empty($duplicates)) {
            return response()->json([
                'success' => false,
                'message' => 'The following IDs are duplicated in the input.',
                'duplicates' => $duplicates,
            ], 422);
        }

        // Step 2: Check for any `mt5_ids` that already exist in the database as `sl_mt5_id`
        $existingSlaves = Slave::whereIn('sl_mt5_id', $mt5_ids)->pluck('sl_mt5_id')->toArray();

        if (!empty($existingSlaves)) {
            return response()->json([
                'success' => false,
                'message' => 'The following IDs already exist as slaves.',
                'existing_ids' => $existingSlaves,
            ], 422);
        }

        // Step 3: If no issues, return success
        return response()->json([
            'success' => true,
            'message' => 'The provided mt5_ids are valid.',
        ], 200);
    }



    public function exportSlaves(Request $request)
    {
        // Validate the request (optional filters)

        // Build the query
        $query = Slave::with(['master', 'server', 'mappedByUser']); // Remove 'sl_mt5_id' from 'with'

        if ($request->master_id) {
            $query->where('master_id', $request->master_id);
        }

        $query->orderBy('created_at', 'desc');

        // Prepare the StreamedResponse for CSV download
        $response = new StreamedResponse(function () use ($query) {
            $handle = fopen('php://output', 'w');
            // Write the headers
            fputcsv($handle, [
                'ID',
                'Slave MT5 ID',
                'Master MT5 ID',
                'Master Name',
                'Risk Approach',
                'Multiplier',
                'Fixed Balance',
                'Commission %',
                'Copy SL',
                'Copy TP',
                'Reversed Trade',
                'Status',
            ]);

            // Write each row of the query result
            $query->chunk(100, function ($slaves) use ($handle) {
                foreach ($slaves as $slave) {
                    fputcsv($handle, [
                        $slave->id, // ID
                        $slave->sl_mt5_id ?? '-', // Slave MT5 ID
                        $slave->master->mc_mt5_id ?? '-', // Master ID
                        $slave->master->mc_name ?? '-', // Master Name
                        $slave->risk_approach ?? '-', // Risk Approach
                        $slave->multiplier ?? '-', // Multiplier
                        $slave->risk_approach === 'FBMUL' ? $slave->fixed_balance : '-', // Fixed Balance
                        $slave->commission_percentage ?? '-', // Multiplier
                        $slave->copy_sl ? 'ENABLED' : 'DISABLED', // Copy SL
                        $slave->copy_tp ? 'ENABLED' : 'DISABLED', // Copy TP
                        $slave->is_reverse ? 'ENABLED' : 'DISABLED', // Reversed Copy SL
                        $slave->status ? 'ACTIVE' : 'INACTIVE', // Status
                    ]);
                }
            });

            fclose($handle);
        });

        // Set headers for file download
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="slaves_list.csv"');

        return $response;
    }


    public function slavesList(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'master_id' => 'sometimes|integer|exists:tb_masters,id',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'page' => 'sometimes|integer|min:1',  // Validate page parameter
            'search' => 'sometimes|max:255',
        ]);

        // Retrieve query parameters
        $master_id = $request->master_id;
        $per_page = $request->per_page;
        $search = $request->search;

        // Build the query
        $query = Slave::with(['master', 'server', 'mappedByUser']); // Remove 'sl_mt5_id' from 'with'

        // Apply filtering if master_id is provided
        if ($master_id) {
            $query->where('master_id', $master_id);
        }

        // Apply search filter for sl_mt5_id if provided
        if ($search) {
            $query->where('sl_mt5_id', 'LIKE', "%{$search}%");
        }

        $query->orderBy('created_at', 'desc');
        // Execute the query with pagination (page is handled automatically)
        $slaves = $query->paginate($per_page);

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $slaves,
        ], 200);
    }
    /**
     * Show a specific slave by ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
           // 'id' => 'required|integer|exists:tb_slaves,id',
            'sl_mt5_id' => 'required|exists:tb_slaves,sl_mt5_id',
        ]);

        // Retrieve slave by ID
        $slave = Slave::with(['master', 'server', 'mappedByUser'])
            //->find($request->input('id'));
            ->where('sl_mt5_id', $request->sl_mt5_id)
            ->first();

        // Check if slave exists
        if (!$slave) {
            return response()->json([
                'success' => false,
                'message' => 'Slave not found.',
            ], 404);
        }

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $slave,
        ], 200);
    }


    /**
     * Create a new slave.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'master_id' => 'required|integer|exists:tb_masters,id',
            'sl_mt5_id' => 'required|integer|unique:tb_slaves,sl_mt5_id',
            'server_id' => 'required|integer|exists:tb_meta_servers,id',
            'mapped_by' => 'required|integer|exists:tb_staff_users,id',
            'is_config_unique' => 'required|boolean',
            'risk_approach' => 'nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'lot_size' => 'nullable|numeric',
            'multiplier' => 'required|numeric|min:0',
            'fixed_balance' => 'required|numeric|min:0',
            'commission_percentage' => 'numeric',
            'copy_sl' => 'required|boolean',
            'copy_tp' => 'required|boolean',
            'is_reverse' => 'required|boolean',
            'status' => 'required|boolean',
            'is_live' => 'required|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        // Create the slave record
        $slave = Slave::create($validated);

        // Set is_synced to false for the associated server
        MetaServer::where('id', $slave->server_id)->update(['is_synced' => false]);


        // Return the response
        return response()->json([
            'success' => true,
            'data' => $slave,
            'message' => 'Slave created successfully.',
        ], 201);
    }

    public function bulkStore(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'master_id' => 'required|integer|exists:tb_masters,id',
            'sl_mt5_ids' => 'required|string', // Comma-separated string of sl_mt5_ids
            'server_id' => 'required|integer|exists:tb_meta_servers,id',
            'mapped_by' => 'required|integer|exists:tb_staff_users,id',
            'is_config_unique' => 'required|boolean',
            'risk_approach' => 'nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'lot_size' => 'nullable|numeric',
            'multiplier' => 'required|numeric|min:0',
            'fixed_balance' => 'required|numeric|min:0',
            'commission_percentage' => 'numeric',
            'copy_sl' => 'required|boolean',
            'copy_tp' => 'required|boolean',
            'is_reverse' => 'required|boolean',
            'status' => 'required|boolean',
            'is_live' => 'required|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        // Extract and split the sl_mt5_ids
        $sl_mt5_ids = explode(',', $validated['sl_mt5_ids']);
        $sl_mt5_ids = array_map('trim', $sl_mt5_ids); // Trim any whitespace

        // Check for duplicates in the provided IDs
        $duplicates = array_unique(array_diff_assoc($sl_mt5_ids, array_unique($sl_mt5_ids)));
        if (!empty($duplicates)) {
            return response()->json([
                'success' => false,
                'message' => 'The following sl_mt5_ids are duplicated in the input.',
                'duplicates' => $duplicates,
            ], 422);
        }

        // Check for already existing sl_mt5_id in the database
        $existing_ids = Slave::whereIn('sl_mt5_id', $sl_mt5_ids)->pluck('sl_mt5_id')->toArray();
        if (!empty($existing_ids)) {
            return response()->json([
                'success' => false,
                'message' => 'The following sl_mt5_ids already exist in the database.',
                'existing_ids' => $existing_ids,
            ], 422);
        }

        // Prepare and insert each slave record
        $inserted_slaves = [];
        foreach ($sl_mt5_ids as $sl_mt5_id) {
            $slave_data = array_merge($request->only([
                'master_id',
                'server_id',
                'mapped_by',
                'is_config_unique',
                'risk_approach',
                'commission_type',
                'lot_size',
                'multiplier',
                'fixed_balance',
                'commission_percentage',
                'copy_sl',
                'copy_tp',
                'is_reverse',
                'status',
                'is_live',
                'ex_client_id',
                'ex_client_name',
                'ex_client_email',
            ]), [
                'sl_mt5_id' => $sl_mt5_id,
            ]);

            $inserted_slaves[] = Slave::create($slave_data);
        }

        // Set is_synced to false for the associated server
        MetaServer::where('id', $request->server_id)->update(['is_synced' => false]);

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $inserted_slaves,
            'message' => count($inserted_slaves) . ' slaves added successfully.',
        ], 201);
    }

    /**
     * Update an existing slave.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'id' => 'required|integer|exists:tb_slaves,id',
            'master_id' => 'sometimes|integer|exists:tb_masters,id',
            'sl_mt5_id' => 'sometimes|integer',
            'server_id' => 'sometimes|integer|exists:tb_meta_servers,id',
            'mapped_by' => 'sometimes|integer|exists:tb_staff_users,id',
            'is_config_unique' => 'sometimes|boolean',
            'risk_approach' => 'nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'lot_size' => 'nullable|numeric',
            'multiplier' => 'sometimes|numeric|min:0',
            'fixed_balance' => 'sometimes|numeric|min:0',
            'commission_percentage' => 'numeric',
            'copy_sl' => 'sometimes|boolean',
            'copy_tp' => 'sometimes|boolean',
            'is_reverse' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
            'is_live' => 'sometimes|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        // Find the slave by ID
        $slave = Slave::find($request->input('id'));

        // Update the slave with validated data
        $slave->update($validated);

        // Set is_synced to false for the associated server
        MetaServer::where('id', $slave->server_id)->update(['is_synced' => false]);


        // Return the response
        return response()->json([
            'success' => true,
            'data' => $slave,
            'message' => 'Slave updated successfully.',
        ], 200);
    }


    public function bulkUpdate(Request $request)
    {
        // Validate the bulk update request
        $validated = $request->validate([
            'ids' => 'required|array|min:1', // 'ids' field must contain an array of at least one element
            'ids.*' => 'integer|exists:tb_slaves,id', // Each ID in the array must be an existing slave ID
            'master_id' => 'sometimes|integer|exists:tb_masters,id',
            'sl_mt5_id' => 'sometimes|integer',
            'server_id' => 'sometimes|integer|exists:tb_meta_servers,id',
            'mapped_by' => 'sometimes|integer|exists:tb_staff_users,id',
            'is_config_unique' => 'sometimes|boolean',
            'risk_approach' => 'nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'lot_size' => 'nullable|numeric',
            'multiplier' => 'sometimes|numeric|min:0',
            'fixed_balance' => 'sometimes|numeric|min:0',
            'commission_percentage' => 'numeric',
            'copy_sl' => 'sometimes|boolean',
            'copy_tp' => 'sometimes|boolean',
            'is_reverse' => 'sometimes|boolean',
            'status' => 'sometimes|boolean',
            'is_live' => 'sometimes|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        // Retrieve the IDs to update
        $ids = $validated['ids'];
        unset($validated['ids']); // Remove 'ids' from the validated data since it's not part of the attributes to update

        // Retrieve the server IDs for all the provided slave IDs before the update
        $server_ids = Slave::whereIn('id', $ids)->pluck('server_id')->unique();

        // Perform the update for all selected IDs
        Slave::whereIn('id', $ids)->update($validated);

        // Update `is_synced` to false for associated servers after the update
        MetaServer::whereIn('id', $server_ids)->update(['is_synced' => false]);

        // Return the response
        return response()->json([
            'success' => true,
            'message' => count($ids) . ' slaves updated successfully.',
        ], 200);
    }


    public function destroy(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'id' => 'required|integer|exists:tb_slaves,id',
        ]);

        $slave = Slave::find($request->id);
        $server_id = $slave->server_id;
        $slave->delete();

        // Set is_synced to false for the associated server
        MetaServer::where('id', $server_id)->update(['is_synced' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Slave deleted successfully.',
        ], 200);
    }

    public function bulkDestroy(Request $request)
    {
        // Validate the request to ensure IDs are provided and exist in the database
        $validated = $request->validate([
            'ids' => 'required|array|min:1', // Requires 'ids' to be an array with at least one element
            'ids.*' => 'integer|exists:tb_slaves,id', // Each ID in the array must exist in the database
        ]);

        // Retrieve server IDs for all the provided slave IDs (to update is_synced)
        $server_ids = Slave::whereIn('id', $validated['ids'])->pluck('server_id')->unique();

        // Perform the bulk delete
        Slave::whereIn('id', $validated['ids'])->delete();

        // Set is_synced to false for associated servers
        MetaServer::whereIn('id', $server_ids)->update(['is_synced' => false]);

        // Return the response
        return response()->json([
            'success' => true,
            'message' => count($validated['ids']) . ' slaves deleted successfully.',
        ], 200);
    }

public function latestManagedSlaves()
{
    $latestSlaves = Slave::with(['master', 'server', 'mappedByUser'])
                        ->orderBy('created_at', 'desc')
                        ->limit(20)
                        ->get();


    return response()->json([
        'success' => true,
        'data' => $latestSlaves,
    ], 200);
}


}
