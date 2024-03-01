<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ms_entities', function (Blueprint $table) {
            $table->id();
            $table->string("keyword", 64);
            $table->string("name_RU", 64); //ru для дальнейшего расширения и возможного перевода на языки.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ms_entities');
    }
};
