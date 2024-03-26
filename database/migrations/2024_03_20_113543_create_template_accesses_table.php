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
        Schema::create('template_accesses', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('template_auto_settings_id')->nullable();
            $table->foreign('template_auto_settings_id')->references('id')->on('template_auto_settings')->onDelete('cascade');

            $table->unsignedBigInteger('template_id')->nullable();
            $table->foreign('template_id')->references('id')->on('templates');

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
        Schema::dropIfExists('template_accesses');
    }
};
