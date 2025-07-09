<?php

namespace App\Http\Controllers;

use App\Models\Master;
use App\Models\Slave;
use App\Models\MetaServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MasterController extends Controller
{
    /**
     * List all masters.
     */
    public function index(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'server_id' => 'sometimes|integer',
        ]);

        // Retrieve the server_id from the request
        $server_id = $request->input('server_id');

        // Build the query
        $query = Master::with(['server', 'mapper', 'creator', 'updater']);

        // Apply filtering if server_id is provided
        if ($server_id) {
            $query->where('server_id', $server_id);
        }

        // Execute the query and get the results
        $masters = $query->get();

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $masters,
        ], 200);
    }


    /**
     * Show a specific master.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mc_mt5_id' => 'required|exists:tb_masters,mc_mt5_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $master = Master::with(['server', 'mapper', 'creator', 'updater','slaves', 'slaveServers'])
        ->where('mc_mt5_id', $request->mc_mt5_id)
        ->first();

        return response()->json([
            'success' => true,
            'data' => $master,
        ], 200);
    }

    /**
     * Create a new master.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mc_name' => 'required|string|max:255',
            'mc_mt5_id' => 'required',
            'server_id' => 'required|exists:tb_meta_servers,id',
            'performance_matrix' => 'required|numeric|min:0',
            'risk_factor' => 'required|numeric|min:0',
            'is_config_identical' => 'required|boolean',
            'risk_approach' => 'nullable|in:FIXL,LMUL,EMUL,BMUL,FBMUL',
            'lot_size' => 'nullable|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0',
            'commission_percentage' => 'numeric',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'fixed_balance' => 'nullable|numeric|min:0',
            'copy_sl' => 'required|boolean',
            'copy_tp' => 'required|boolean',
            'is_reverse' => 'required|boolean',
            'status' => 'required|boolean',
            'is_live' => 'required|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $authenticatedUserId = Auth::id();

        $master = Master::create([
            'mc_name' => $request->mc_name,
            'mc_mt5_id' => $request->mc_mt5_id,
            'server_id' => $request->server_id,
            'mapped_by' => $authenticatedUserId,
            'performance_matrix' => $request->performance_matrix,
            'risk_factor' => $request->risk_factor,
            'is_config_identical' => $request->is_config_identical,
            'risk_approach' => $request->risk_approach,
            'lot_size' => $request->lot_size,
            'multiplier' => $request->multiplier,
            'fixed_balance' => $request->fixed_balance,
            'commission_percentage' => $request->commission_percentage,
            'commission_type' => $request->commission_type,
            'copy_sl' => $request->copy_sl,
            'copy_tp' => $request->copy_tp,
            'is_reverse' => $request->is_reverse,
            'status' => $request->status,
            'is_live' => $request->is_live,
            'ex_client_id' => $request->ex_client_id,
            'ex_client_name' => $request->ex_client_name,
            'ex_client_email' => $request->ex_client_email,
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);

        MetaServer::where('id', $master->server_id)->update(['is_synced' => false]);


        return response()->json([
            'success' => true,
            'message' => 'Master created successfully.',
            'data' => $master,
        ], 201);
    }

    /**
     * Update an existing master.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tb_masters,id',
            'mc_name' => 'sometimes|required|string|max:255',
            'mc_mt5_id' => 'sometimes|required|integer',
            'server_id' => 'sometimes|required|exists:tb_meta_servers,id',
            'performance_matrix' => 'sometimes|required|numeric|min:0',
            'risk_factor' => 'sometimes|required|numeric|min:0',
            'is_config_identical' => 'sometimes|required|boolean',
            'risk_approach' => 'sometimes|nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'lot_size' => 'sometimes|nullable|numeric|min:0',
            'multiplier' => 'sometimes|nullable|numeric|min:0',
            'fixed_balance' => 'sometimes|nullable|numeric|min:0',
            'commission_percentage' => 'numeric',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'copy_sl' => 'sometimes|required|boolean',
            'copy_tp' => 'sometimes|required|boolean',
            'is_reverse' => 'sometimes|required|boolean',
            'status' => 'sometimes|required|boolean',
            'is_live' => 'sometimes|required|boolean',
            'ex_client_id' => 'nullable|integer',
            'ex_client_name' => 'nullable|string|max:255',
            'ex_client_email' => 'nullable|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }


        $master = Master::find($request->id);

        // Update only provided fields
        $master->fill($request->all());
        $master->updated_by = Auth::id();
        $master->save();

        // Set is_synced to false for the associated server
        MetaServer::where('id', $master->server_id)->update(['is_synced' => false]);


        return response()->json([
            'success' => true,
            'message' => 'Master updated successfully.',
            'data' => $master,
        ], 200);
    }

    /**
     * Delete a master.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tb_masters,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $master = Master::find($request->id);
        $server_id = $master->server_id;
        $master->delete();

        // Set is_synced to false for the associated server
        MetaServer::where('id', $server_id)->update(['is_synced' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Master deleted successfully.',
        ], 200);
    }


    public function generateSlavesReport($master_id)
    {
        $master = Master::with('slaves')->find($master_id);

        if (!$master) {
            return response()->json(['message' => 'Master not found'], 404);
        }

        $slaves = $master->slaves;
        $m_mt5id= $master->mc_mt5_id;
        // Create CSV response
        $response = new StreamedResponse(function() use ($slaves,$m_mt5id) {
            $handle = fopen('php://output', 'w');
            // Add the CSV headers
            fputcsv($handle, [
                'Slave ID','Master ID', 'MT5 ID', 'Risk Approach',
                'Commission Percentage', 'Commission Type', 'Lot Size', 'Multiplier',
                'Fixed Balance', 'Copy SL', 'Copy TP', 'Is Reverse'
            ]);

            // Add slave data rows
            foreach ($slaves as $slave) {
                fputcsv($handle, [
                    $slave->id,
                    $m_mt5id,
                    $slave->sl_mt5_id,
                    $slave->risk_approach,
                    $slave->commission_percentage,
                    $slave->commission_type,
                    $slave->lot_size,
                    $slave->multiplier,
                    $slave->fixed_balance,
                    $slave->copy_sl ? 'ACTIVE' : 'NO',
                    $slave->copy_tp ? 'ACTIVE' : 'NO',
                    $slave->is_reverse ? 'ACTIVE' : 'NO'
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="slaves_report_master_' . $master_id . '.csv"');

        return $response;
    }

    public function getStats()
{

    $lastWeek = now()->subWeek()->startOfWeek();
    $lastMonth = now()->subMonth()->startOfMonth();


    $totalSlavesLastWeek = Slave::whereBetween('created_at', [$lastWeek, now()])->count();
    $totalSlavesLastMonth = Slave::whereBetween('created_at', [$lastMonth, now()])->count();


    $totalMastersLastWeek = Master::whereBetween('created_at', [$lastWeek, now()])->count();
    $totalMastersLastMonth = Master::whereBetween('created_at', [$lastMonth, now()])->count();


    $totalActiveSlaves = Slave::where('status', 1)->count();
    $totalInactiveSlaves = Slave::where('status', 0)->count();


    $totalActiveMasters = Master::where('status', 1)->count();
    $totalInactiveMasters = Master::where('status', 0)->count();

    // Return response
    return response()->json([
        'success' => true,
        'data' => [
            'total_slaves_last_week' => $totalSlavesLastWeek,
            'total_slaves_last_month' => $totalSlavesLastMonth,
            'total_masters_last_week' => $totalMastersLastWeek,
            'total_masters_last_month' => $totalMastersLastMonth,
            'total_active_slaves' => $totalActiveSlaves,
            'total_inactive_slaves' => $totalInactiveSlaves,
            'total_active_masters' => $totalActiveMasters,
            'total_inactive_masters' => $totalInactiveMasters,
        ],
    ], 200);
}
public function getMastersWithSlaveCount()
{
    try {
        
        $masters = Master::withCount('slaves')->get();

        return response()->json([
            'success' => true,
            'masters' => $masters,
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage(),
        ], 400);
    }
}

}
