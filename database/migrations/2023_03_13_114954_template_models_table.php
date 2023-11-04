<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('template_models', function (Blueprint $table) {
            $table->id();
            $table->string('accountId');
            $table->string('organId');
            $table->string('name');
            $table->string('name_uid');

            $table->longText('message');

            $table->timestamps();
        });
    }


    public function down()
    {
        Schema::dropIfExists('template_models');
    }
};
