<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_orders', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary key
            $table->unsignedBigInteger('master_id'); // Reference to tb_masters
            $table->unsignedBigInteger('slave_id')->nullable(); // Reference to tb_slaves (nullable for master-only orders)
            $table->bigInteger('mt_user_id'); // MT5 user ID (either master or slave)
            $table->bigInteger('order_id'); // Unique order ID from MT5
            $table->enum('order_type', ['buy', 'sell']); // BUY or SELL action
            $table->enum('order_kind', ['market', 'limit', 'stop', 'stop_limit'])->nullable(); // Type of order
            $table->string('symbol', 255)->nullable(); // Trading symbol (e.g., EURUSD)
            $table->double('price')->nullable(); // Order price
            $table->double('volume')->nullable(); // Volume traded
            $table->enum('order_status', ['pending', 'executed', 'canceled', 'failed'])->default('pending'); // Order status
            $table->double('stop_loss')->nullable(); // Stop-loss level
            $table->double('take_profit')->nullable(); // Take-profit level
            $table->datetime('order_date'); // Order placement date and time
            $table->datetime('execution_date')->nullable(); // Order execution date and time
            $table->double('executed_price')->nullable(); // Executed price
            $table->double('profit_loss')->nullable(); // Profit/loss for executed orders
            $table->unsignedBigInteger('server_id'); // Server reference
            $table->timestamps(); // created_at and updated_a
            // Indexes
            $table->index('mt_user_id');
            $table->index('order_id');
            $table->index('symbol');
            $table->index('order_status');
            $table->index('server_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_orders');
    }
};
