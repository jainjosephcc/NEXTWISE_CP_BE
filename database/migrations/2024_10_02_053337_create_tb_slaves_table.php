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
        Schema::create('tb_slaves', function (Blueprint $table) {
            $table->bigIncrements('id'); // 1. Primary Key

            $table->unsignedBigInteger('master_id'); // 2. Foreign Key to tb_masters
            $table->bigInteger('sl_mt5_id'); // 3. Index
            $table->unsignedBigInteger('server_id'); // 4. Foreign Key to tb_masters (replaces client_id)
            $table->unsignedBigInteger('mapped_by'); // 5. Foreign Key to tb_staff_users

            $table->boolean('is_config_unique')->default(false); // 6. Default 0
            $table->enum('risk_approach', ['FIXL', 'LMUL', 'BMUL', 'FBMUL'])->nullable(); // 7. Enum
            $table->decimal('lot_size', 10, 2)->nullable(); // 8. Decimal
            $table->decimal('multiplier', 10, 2)->default(0.00); // 9. Decimal with default 0.00
            $table->decimal('fixed_balance', 10, 2)->default(0.00); // 10. Decimal with default 0.00
            $table->boolean('copy_sl')->default(false); // 11. Default 0
            $table->boolean('copy_tp')->default(false); // 12. Default 0
            $table->boolean('is_reverse')->default(false); // 13. Default 0
            $table->boolean('status')->default(true); // 14. Default 1
            $table->boolean('is_live')->default(true); // 15. Default 1

            $table->timestamps(); // 16 & 17. created_at and updated_at

            // Indexes
            $table->index('master_id');
            $table->index('sl_mt5_id');
            $table->index('server_id');
            $table->index('mapped_by');

            // Foreign Key Constraints
            $table->foreign('master_id')
                ->references('id')
                ->on('tb_masters')
                ->onDelete('cascade');

            $table->foreign('server_id')
                ->references('id')
                ->on('tb_masters')
                ->onDelete('cascade');

            $table->foreign('mapped_by')
                ->references('id')
                ->on('tb_staff_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tb_slaves');
    }
};
