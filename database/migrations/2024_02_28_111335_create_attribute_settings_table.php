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
        Schema::create('attribute_settings', function (Blueprint $table) {
            $table->id();
            
            $table->string("entity_type", 64);
            $table->string('name', 255);
            $table->uuid("attribute_id");
            //$table->string("upload", 1);

            $table->unsignedBigInteger('main_settings_id')->nullable();
            $table->foreign('main_settings_id')->references('id')->on('main_settings')->onDelete('cascade');

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
        Schema::dropIfExists('attribute_settings');
    }
};
