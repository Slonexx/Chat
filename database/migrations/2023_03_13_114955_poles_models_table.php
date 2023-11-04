<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('poles_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('name');
            $table->string('name_uid');

            $table->string('i')->nullable();
            $table->string('pole')->nullable();
            $table->string('add_pole')->nullable();
            $table->string('entity')->nullable();
            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('poles_models');
    }
};
