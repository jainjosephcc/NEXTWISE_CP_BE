<?php

use App\Http\Controllers\GroupCopierController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\MetaServerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SlaveController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/plugin/login', [AuthController::class, 'pluginLogin']);
//Route::post('/register', [AuthController::class, 'register']); // Comment out if registration is not needed
Route::get('/plugin/get-masters-with-slaves', [MetaServerController::class, 'getMastersWithSlaves']);

// Protected Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);


    Route::post('/store-trades', [\App\Http\Controllers\CopiedTradeController::class, 'storeTrades']);


    //TO CHECK SYNC STATUS FROM META
    Route::get('/plugin/get-sync-status', [MetaServerController::class, 'getSyncStatus']);
    Route::get('/plugin/get-masters-with-slaves-old', [MetaServerController::class, 'getMastersWithSlaves']);


    //STORE ORDERS FROM PLUGIN
    Route::post('/orders/batch', [OrderController::class, 'storeBatch']);

    Route::get('/reports-open-positions/{mt5id}/{page}', [\App\Http\Controllers\TradesReportsController::class, 'getMt5Positions']);
    Route::get('/reports-get-deals/{mt5id}/{page}', [\App\Http\Controllers\TradesReportsController::class, 'getMt5Deals']);
    Route::get('/masters-open-positions', [\App\Http\Controllers\TradesReportsController::class, 'getMastersWithOpenPositions']);
    Route::get('/trade-statistics', [\App\Http\Controllers\TradesReportsController::class, 'generateTradeStatistics']);



    // List all meta servers
    Route::get('/meta-servers', [MetaServerController::class, 'index']);
    // Show a single meta server (ID passed in POST form data)
    Route::post('/meta-servers/show', [MetaServerController::class, 'show']);
    // Create a new meta server
    Route::post('/meta-servers/store', [MetaServerController::class, 'store']);
    // Update an existing meta server (ID passed in POST form data)
    Route::post('/meta-servers/update', [MetaServerController::class, 'update']);
    // Delete a meta server (ID passed in POST form data)
    Route::post('/meta-servers/delete', [MetaServerController::class, 'destroy']);


    // List all masters
    Route::get('/masters', [MasterController::class, 'index']);
    // Show a specific master (ID passed in POST form data)
    Route::post('/masters/show', [MasterController::class, 'show']);
    // Create a new master
    Route::post('/masters/store', [MasterController::class, 'store']);
    // Update an existing master (ID passed in POST form data)
    Route::post('/masters/update', [MasterController::class, 'update']);
    // Delete a master (ID passed in POST form data)
    Route::post('/masters/delete', [MasterController::class, 'destroy']);
    Route::get('/masters/{master_id}/slaves-report', [\App\Http\Controllers\MasterController::class, 'generateSlavesReport']);

    Route::get('/dashboard-stats', [MasterController::class, 'getStats']);
    Route::get('/masters-slave-count', [MasterController::class, 'getMastersWithSlaveCount']);



    // List all slaves (optionally filtered by master_id)
    Route::get('/slaves', [SlaveController::class, 'index']);
    Route::post('/slaves-list', [SlaveController::class, 'slavesList']);
    Route::post('/export-slaves', [SlaveController::class, 'exportSlaves']);
    Route::post('/validate-bulk-slave-ids', [SlaveController::class, 'validateMT5Ids']);
    // Show a specific slave (ID passed in POST form data)
    Route::post('/slaves/show', [SlaveController::class, 'show']);
    // Create a new slave
    Route::post('/slaves/store', [SlaveController::class, 'store']);
    Route::post('/slaves/bulk-store', [SlaveController::class, 'bulkStore']);
    // Update an existing slave (ID passed in POST form data)
    Route::post('/slaves/update', [SlaveController::class, 'update']);
    Route::post('/slaves/bulk-update', [SlaveController::class, 'bulkUpdate']);
    // Delete a slave (ID passed in POST form data)
    Route::post('/slaves/delete', [SlaveController::class, 'destroy']);
    Route::post('/slaves/bulk-delete', [SlaveController::class, 'bulkDestroy']);
    Route::get('/slaves/latest-managed-slaves', [SlaveController::class, 'latestManagedSlaves']);



    // List all group copiers
    Route::get('/group-copiers', [GroupCopierController::class, 'index']);
// Show a specific group copier (ID passed in POST form data)
    Route::post('/group-copiers/show', [GroupCopierController::class, 'show']);
// Create a new group copier
    Route::post('/group-copiers/store', [GroupCopierController::class, 'store']);
// Update an existing group copier (ID passed in POST form data)
    Route::post('/group-copiers/update', [GroupCopierController::class, 'update']);
// Delete a group copier (ID passed in POST form data)
    Route::post('/group-copiers/delete', [GroupCopierController::class, 'destroy']);

});
