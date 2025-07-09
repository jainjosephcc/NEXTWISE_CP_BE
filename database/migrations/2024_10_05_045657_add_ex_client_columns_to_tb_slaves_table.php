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
            $table->bigInteger('ex_client_id')->nullable()->after('is_live');
            $table->string('ex_client_name', 255)->nullable()->after('ex_client_id');
            $table->string('ex_client_email', 255)->nullable()->after('ex_client_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_slaves', function (Blueprint $table) {
            $table->dropColumn(['ex_client_id', 'ex_client_name', 'ex_client_email']);
        });
    }
};
