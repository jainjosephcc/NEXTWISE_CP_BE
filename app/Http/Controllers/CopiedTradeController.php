<?php

namespace App\Http\Controllers;

use App\Models\CopiedTrade;
use Illuminate\Http\Request;

class CopiedTradeController extends Controller
{
    // ✅ Store a new copied trade
    public function storeTrades(Request $request)
    {
        $validated = $request->validate([
            'master_login' => 'required|integer',
            'slave_login' => 'required|integer',
            'symbol' => 'required|string|max:50',
            'trade_type' => 'required|in:buy,sell',
            'volume' => 'required|numeric',
            'price' => 'required|numeric',
            'profit' => 'nullable|numeric',
            'status' => 'required|in:success,failed',
            'comment' => 'nullable|string',
        ]);

        $trade = CopiedTrade::create($validated);

        return response()->json([
            'message' => 'Copied trade stored successfully.',
            'data' => $trade
        ], 201);
    }

    // ✅ Fetch all copied trades (paginated)
    public function index()
    {
        $trades = CopiedTrade::orderBy('created_at', 'desc')->paginate(20);

        return response()->json($trades);
    }

    // ✅ Fetch a single copied trade by ID
    public function show($id)
    {
        $trade = CopiedTrade::findOrFail($id);

        return response()->json($trade);
    }
}
