<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('employee_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('employeeId');
            $table->string('employeeName');

            $table->string('email');
            $table->string('password');
            $table->string('appId');

            $table->string('access');


            $table->string('cabinetUserId');
            $table->string('accessToken');
            $table->string('refreshToken');

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('employee_models');
    }
};
