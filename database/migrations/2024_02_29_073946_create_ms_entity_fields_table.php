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
        Schema::create('ms_entity_fields', function (Blueprint $table) {
            $table->id();
            $table->string("keyword", 64);
            $table->string("name_RU", 64); //ru для дальнейшего расширения и возможного перевода на языки.
            $table->string("expand_filter", 128)->nullable();

            $table->unsignedBigInteger('ms_entities_id')->nullable();
            $table->foreign('ms_entities_id')->references('id')->on('ms_entities')->onDelete('cascade');

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
        Schema::dropIfExists('ms_entity_fields');
    }
};
