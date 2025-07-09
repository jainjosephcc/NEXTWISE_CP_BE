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
        Schema::table('tb_meta_servers', function (Blueprint $table) {
            // Add the foreign key constraint
            $table->foreign('manager_id')
                ->references('id')
                ->on('tb_staff_users')
                ->onDelete('cascade') // Adjust as needed: cascade, restrict, set null, etc.
                ->onUpdate('cascade'); // Adjust as needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_meta_servers', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['manager_id']);
        });
    }
};
