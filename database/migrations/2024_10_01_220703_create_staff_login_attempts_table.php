<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('staff_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('attempted_at')->nullable();
            $table->timestamps();

            // Corrected Foreign Key Reference
            $table->foreign('staff_id')->references('id')->on('tb_staff_users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_login_attempts');
    }
};
