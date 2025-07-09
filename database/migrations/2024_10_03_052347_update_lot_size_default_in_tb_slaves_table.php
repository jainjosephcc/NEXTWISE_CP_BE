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
        // Update existing NULL values to 0.00
        DB::table('tb_slaves')->whereNull('lot_size')->update(['lot_size' => 0.00]);

        // Modify 'lot_size' column to have a default value of 0.00
        Schema::table('tb_slaves', function (Blueprint $table) {
            $table->decimal('lot_size', 10, 2)->default(0.00)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'lot_size' column to its previous state (nullable, no default value)
        Schema::table('tb_slaves', function (Blueprint $table) {
            $table->decimal('lot_size', 10, 2)->nullable()->default(null)->change();
        });
    }
};
