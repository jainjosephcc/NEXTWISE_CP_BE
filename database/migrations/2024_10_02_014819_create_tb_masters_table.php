<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTbMastersTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tb_masters', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key

            $table->string('mc_name', 255);
            $table->bigInteger('mc_mt5_id')->unsigned(); // Not a foreign key
            $table->bigInteger('server_id')->unsigned(); // Foreign Key

            // Foreign Keys
            $table->foreign('server_id')
                ->references('id')
                ->on('tb_meta_servers')
                ->onDelete('cascade'); // Adjust onDelete behavior as needed

            $table->bigInteger('mapped_by')->unsigned();
            $table->decimal('performance_matrix', 10, 2)->default(0.00);
            $table->decimal('risk_factor', 10, 2)->default(0.00);
            $table->boolean('is_config_identical')->default(false);
            $table->enum('risk_approach', ['FIXL', 'LMUL', 'BMUL', 'FBMUL'])->nullable();
            $table->decimal('lot_size', 10, 2)->nullable();
            $table->decimal('multiplier', 10, 2)->nullable();
            $table->decimal('fixed_balance', 10, 2)->nullable();
            $table->boolean('copy_sl')->default(false);
            $table->boolean('copy_tp')->default(false);
            $table->boolean('is_reverse')->default(false);
            $table->boolean('status')->default(true);
            $table->boolean('is_live')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('mapped_by')
                ->references('id')
                ->on('tb_staff_users')
                ->onDelete('restrict'); // Prevent deletion if referenced

            $table->foreign('created_by')
                ->references('id')
                ->on('tb_staff_users')
                ->onDelete('set null'); // Set to null if user is deleted

            $table->foreign('updated_by')
                ->references('id')
                ->on('tb_staff_users')
                ->onDelete('set null'); // Set to null if user is deleted

            // Indexes
            $table->index('mc_mt5_id');
            $table->index('server_id');
            $table->index('mapped_by');
            $table->index('created_by');
            $table->index('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_masters');
    }
}
