<?php

namespace App\Http\Controllers;

use App\Models\GroupCopier;
use App\Models\Master;
use App\Models\MetaServer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MetaServerController extends Controller
{
    // List all meta servers
    public function index()
    {
        // Fetch all meta servers with the manager details
        $metaServers = MetaServer::with('manager')->get();

        // Modify the response to include the manager's name
        $metaServersWithManager = $metaServers->map(function ($server) {
            return [
                'id' => $server->id,
                'company_name' => $server->company_name,
                'server_name' => $server->server_name,
                'server_ip' => $server->server_ip,
                'server_port' => $server->server_port,
                'api_url' => $server->api_url,
                'server_type' => $server->server_type,
                'ssl_enabled' => $server->ssl_enabled,
                'manager_id' => $server->manager_id,
                'manager_name' => $server->manager ? $server->manager->staff_name : null, // Fetch the manager's name
                'status' => $server->status,
                'description' => $server->description,
                'created_by' => $server->created_by,
                'updated_by' => $server->updated_by,
                'created_at' => $server->created_at,
                'updated_at' => $server->updated_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $metaServersWithManager,
        ], 200);
    }


    // Show a specific meta server
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tb_meta_servers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $metaServer = MetaServer::find($request->id);

        return response()->json([
            'success' => true,
            'data' => $metaServer,
        ], 200);
    }

    // Create a new meta server
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'server_name'  => 'required|string|max:255',
            'server_ip'    => 'required|ip',
            'manager_id'   => 'required',
            // Add other validation rules for additional fields
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $metaServer = new MetaServer();
        $metaServer->company_name = $request->company_name;
        $metaServer->server_name  = $request->server_name;
        $metaServer->server_ip    = $request->server_ip;
        $metaServer->manager_id   = $request->manager_id;
        $metaServer->created_by   = Auth::id();
        $metaServer->updated_by   = Auth::id();
        // Set other fields if present
        $metaServer->save();

        return response()->json([
            'success' => true,
            'message' => 'Meta server created successfully.',
            'data'    => $metaServer,
        ], 201);
    }

    // Update an existing meta server
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'           => 'required|exists:tb_meta_servers,id',
            'company_name' => 'sometimes|required|string|max:255',
            'server_name'  => 'sometimes|required|string|max:255',
            'server_ip'    => 'sometimes|required|ip',
            'manager_id'   => 'sometimes|required',
            // Add other validation rules for additional fields
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $metaServer = MetaServer::find($request->id);

        // Update only provided fields
        $metaServer->fill($request->all());
        $metaServer->updated_by = Auth::id();
        $metaServer->is_synced = false;  // Set is_synced to false
        $metaServer->save();

        return response()->json([
            'success' => true,
            'message' => 'Meta server updated successfully.',
            'data'    => $metaServer,
        ], 200);
    }

    // Delete a meta server
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:tb_meta_servers,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $metaServer = MetaServer::find($request->id);
        $metaServer->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meta server deleted successfully.',
        ], 200);
    }

    public function getSyncStatus()
    {
        try {
            // Get the authenticated user's ID
            $userId = Auth::id();

            // Find the entry in tb_meta_servers where manager_id matches the authenticated user ID and status is 1
            $metaServer = MetaServer::where('manager_id', $userId)->where('status', 1)->first();

            // If no entry is found, return an error response
            if (!$metaServer) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not found.',
                ], 404);
            }

            // Return the sync status
            return response()->json([
                'success' => true,
                'sync_status' => $metaServer->is_synced,
            ], 200);

        } catch (\Exception $e) {
            // Handle any unexpected errors
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the sync status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMastersWithSlaves()
    {
        Log::info('getMastersWithSlaves called', [
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);

        try {
            // Get MetaServer entry (for now, hardcoded id=1)
            $metaServer = MetaServer::where('id', 1)->first();

            if (!$metaServer) {
                Log::warning('No MetaServer found with id 1');
                return response()->json([
                    'success' => false,
                    'message' => 'No server found for the authenticated user.',
                ], 404);
            }

            Log::info('MetaServer found', ['meta_server_id' => $metaServer->id]);

            // Fetch masters with their slaves
            $masters = Master::where('server_id', $metaServer->id)
                ->with(['slaves' => function ($query) {
                    $query->where('status', true)->where('is_live', true)
                        ->select(
                            'id', 'sl_mt5_id', 'master_id', 'is_config_unique',
                            'risk_approach', 'lot_size', 'multiplier',
                            'fixed_balance', 'copy_sl', 'copy_tp', 'commission_percentage', 'commission_type',
                            'is_reverse', 'status', 'is_live'
                        );
                }])
                ->select('id', 'mc_name', 'mc_mt5_id')
                ->get();

            Log::info('Fetched masters', ['count' => $masters->count()]);

            $mastersWithSlaves = [];

            foreach ($masters as $master) {
                if ($master->slaves->isNotEmpty()) {
                    $mastersWithSlaves[] = [
                        'id' => $master->id,
                        'mc_name' => $master->mc_name,
                        'mc_mt5_id' => $master->mc_mt5_id,
                        'slaves' => $master->slaves,
                    ];
                }
            }

            Log::info('Filtered masters with slaves', ['count' => count($mastersWithSlaves)]);

            $groupCopiers = GroupCopier::all();
            Log::info('Fetched group copiers', ['count' => $groupCopiers->count()]);

            $response = [
                'success' => true,
                'data' => $mastersWithSlaves,
            ];

            if ($groupCopiers->isNotEmpty()) {
                $response['group_data'] = $groupCopiers;
            }

            // Update sync status
            $metaServer->is_synced = true;
            $metaServer->last_synced = now();
            $metaServer->save();

            Log::info('MetaServer sync updated', [
                'meta_server_id' => $metaServer->id,
                'last_synced' => $metaServer->last_synced
            ]);

            return response()->json($response, 200);

        } catch (\Exception $e) {
            Log::error('Error in getMastersWithSlaves', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}

