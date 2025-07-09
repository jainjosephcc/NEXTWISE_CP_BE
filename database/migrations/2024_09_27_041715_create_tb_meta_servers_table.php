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
        Schema::create('tb_meta_servers', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->string('company_name');
            $table->string('server_name');
            $table->string('server_ip');
            $table->integer('server_port')->nullable();
            $table->string('api_url')->nullable();
            $table->enum('server_type', ['MT4', 'MT5', 'cTrader', 'Other'])->default('MT5');
            $table->boolean('ssl_enabled')->default(0);
            $table->unsignedBigInteger('manager_id');
            $table->boolean('status')->default(1);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_meta_servers');
    }
};
