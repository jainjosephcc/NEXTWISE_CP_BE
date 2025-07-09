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
        Schema::table('tb_slaves', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['server_id']);

            // Add the new foreign key constraint referencing tb_meta_servers
            $table->foreign('server_id')
                ->references('id')
                ->on('tb_meta_servers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_slaves', function (Blueprint $table) {
            // Drop the foreign key constraint added in the up() method
            $table->dropForeign(['server_id']);

            // Restore the original foreign key constraint referencing tb_masters
            $table->foreign('server_id')
                ->references('id')
                ->on('tb_masters')
                ->onDelete('cascade');
        });
    }
};
