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
        Schema::create('tb_staff_users', function (Blueprint $table) {
            $table->id();
            $table->string('staff_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password'); // Important for authentication
            $table->string('contact_no');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->foreign('group_id')->references('id')->on('tb_staff_groups')->onDelete('set null');
            $table->boolean('active')->default(1);
            $table->boolean('is_deleted')->default(0);
            $table->rememberToken(); // For "remember me" functionality
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tb_staff_users');
    }
};
