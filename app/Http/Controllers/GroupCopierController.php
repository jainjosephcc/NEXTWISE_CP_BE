<?php

namespace App\Http\Controllers;
use App\Models\GroupCopier;
use App\Models\MetaServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class GroupCopierController extends Controller
{
    /**
     * List all group copiers.
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
        $query = GroupCopier::with(['server', 'mappedByUser', 'creator', 'updater']);

        // Apply filtering if server_id is provided
        if ($server_id) {
            $query->where('server_id', $server_id);
        }

        // Execute the query and get the results
        $groupCopiers = $query->get();

        // Return the response
        return response()->json([
            'success' => true,
            'data' => $groupCopiers,
        ], 200);
    }

    /**
     * Show a specific group copier.
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:group_copiers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $groupCopier = GroupCopier::with(['server', 'mappedByUser', 'creator', 'updater'])->find($request->id);

        return response()->json([
            'success' => true,
            'data' => $groupCopier,
        ], 200);
    }

    /**
     * Create a new group copier.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'group_name' => 'required|string|max:255',
            'server_id' => 'required|exists:tb_meta_servers,id',
            'is_config_unique' => 'required|boolean',
            'commission_percentage' => 'numeric',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'risk_approach' => 'nullable|in:FIXL,LMUL,EMUL,BMUL,FBMUL',
            'lot_size' => 'nullable|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0',
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

        $groupCopier = GroupCopier::create([
            'group_name' => $request->group_name,
            'server_id' => $request->server_id,
            'mapped_by' => $authenticatedUserId,
            'is_config_unique' => $request->is_config_unique,
            'commission_percentage' => $request->commission_percentage,
            'commission_type' => $request->commission_type,
            'risk_approach' => $request->risk_approach,
            'lot_size' => $request->lot_size,
            'multiplier' => $request->multiplier,
            'fixed_balance' => $request->fixed_balance,
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

        MetaServer::where('id', $groupCopier->server_id)->update(['is_synced' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Group copier created successfully.',
            'data' => $groupCopier,
        ], 201);
    }

    /**
     * Update an existing group copier.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:group_copiers,id',
            'group_name' => 'sometimes|required|string|max:255',
            'server_id' => 'sometimes|required|exists:tb_meta_servers,id',
            'is_config_unique' => 'sometimes|required|boolean',
            'commission_percentage' => 'numeric',
            'commission_type' => 'nullable|in:ONNET,ONPROFIT',
            'risk_approach' => 'nullable|in:FIXL,LMUL,BMUL,EMUL,FBMUL',
            'lot_size' => 'nullable|numeric|min:0',
            'multiplier' => 'nullable|numeric|min:0',
            'fixed_balance' => 'nullable|numeric|min:0',
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

        $groupCopier = GroupCopier::find($request->id);

        // Update only provided fields
        $groupCopier->fill($request->all());
        $groupCopier->updated_by = Auth::id();
        $groupCopier->save();

        MetaServer::where('id', $groupCopier->server_id)->update(['is_synced' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Group copier updated successfully.',
            'data' => $groupCopier,
        ], 200);
    }

    /**
     * Delete a group copier.
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:group_copiers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $groupCopier = GroupCopier::find($request->id);
        $server_id = $groupCopier->server_id;
        $groupCopier->delete();

        MetaServer::where('id', $server_id)->update(['is_synced' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Group copier deleted successfully.',
        ], 200);
    }
}
