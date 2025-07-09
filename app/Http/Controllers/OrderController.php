<?php

namespace App\Http\Controllers;

use App\Models\TbOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Store a batch of orders.
     */
    public function storeBatch(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'orders' => 'required|array',
            'orders.*.master_id' => 'required|integer',
            'orders.*.mt_user_id' => 'required|integer',
            'orders.*.order_id' => 'required|integer',
            'orders.*.order_type' => 'required|in:buy,sell',
            'orders.*.order_kind' => 'nullable|in:market,limit,stop,stop_limit',
            'orders.*.symbol' => 'nullable|string|max:255',
            'orders.*.price' => 'nullable|numeric',
            'orders.*.volume' => 'nullable|numeric',
            'orders.*.order_status' => 'required|in:pending,executed,canceled,failed',
            'orders.*.order_date' => 'required|date',
            'orders.*.server_id' => 'required|integer',
        ]);

        // Extract orders from the request
        $orders = $validatedData['orders'];

        // Prepare data for batch insertion
        $insertData = [];
        foreach ($orders as $order) {
            $insertData[] = [
                'master_id' => $order['master_id'],
                'slave_id' => $order['slave_id'] ?? null,
                'mt_user_id' => $order['mt_user_id'],
                'order_id' => $order['order_id'],
                'order_type' => $order['order_type'],
                'order_kind' => $order['order_kind'] ?? null,
                'symbol' => $order['symbol'] ?? null,
                'price' => $order['price'] ?? null,
                'volume' => $order['volume'] ?? null,
                'order_status' => $order['order_status'],
                'stop_loss' => $order['stop_loss'] ?? null,
                'take_profit' => $order['take_profit'] ?? null,
                'order_date' => $order['order_date'],
                'execution_date' => $order['execution_date'] ?? null,
                'executed_price' => $order['executed_price'] ?? null,
                'profit_loss' => $order['profit_loss'] ?? null,
                'server_id' => $order['server_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Perform batch insertion using DB::table for efficiency
        try {
            DB::table('tb_orders')->insert($insertData);
            return response()->json(['message' => 'Orders successfully stored'], 201);
        } catch (\Exception $e) {
            // Handle errors (e.g., database errors)
            return response()->json(['message' => 'Failed to store orders', 'error' => $e->getMessage()], 500);
        }
    }
}
