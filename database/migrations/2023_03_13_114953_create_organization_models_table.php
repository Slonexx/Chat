<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('organization_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('organId');
            $table->string('organName');

            $table->string('employeeId');
            $table->string('employeeName');

            $table->string('lineId');
            $table->string('lineName');

            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('employee_models');
    }
};
