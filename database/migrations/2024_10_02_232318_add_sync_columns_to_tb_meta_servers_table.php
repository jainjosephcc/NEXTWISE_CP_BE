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
            $table->boolean('is_synced')->default(false); // Add the is_synced column with default false
            $table->dateTime('last_synced')->nullable(); // Add the last_synced column with default null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_meta_servers', function (Blueprint $table) {
            $table->dropColumn('is_synced');
            $table->dropColumn('last_synced');
        });
    }
};
