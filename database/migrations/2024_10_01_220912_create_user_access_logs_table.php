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
        Schema::create('user_access_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->string('type_of_user');
            $table->unsignedBigInteger('personal_access_token_id');
            $table->unsignedBigInteger('user_id');
            $table->string('ip_address')->nullable();
            $table->string('asn_organization')->nullable();
            $table->timestamps();

            $table->foreign('personal_access_token_id')->references('id')->on('personal_access_tokens')->onDelete('cascade');

            // Corrected Foreign Key Reference
            $table->foreign('user_id')->references('id')->on('tb_staff_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access_logs');
    }
};
