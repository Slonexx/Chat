<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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
        Schema::create('template_auto_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid("uuid")->default(DB::raw('(UUID())'));
            $table->string("entity", 64);
            $table->uuid("status");
            $table->uuid("channel")->nullable();
            $table->uuid("project")->nullable();

            $table->unsignedBigInteger('main_settings_id')->nullable();
            $table->foreign('main_settings_id')->references('id')->on('main_settings')->onDelete('cascade');

            $table->unsignedBigInteger('employee_id')->nullable();
            $table->foreign('employee_id')->references('id')->on('chatapp_employees')->onDelete('cascade');

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
        Schema::dropIfExists('template_auto_settings');
    }
};
