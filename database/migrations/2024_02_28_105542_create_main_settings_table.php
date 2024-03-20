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
        Schema::create('main_settings', function (Blueprint $table) {
            $table->id();

            $table->uuid('account_id')->nullable();
            $table->string("ms_token", 42);
            $table->string("app_id", 20)->nullable();
            $table->string("login", 255)->nullable();
            $table->string("password", 255)->nullable();

            //$table->string("token", 128)->nullable();
            $table->boolean("is_activate")->nullable();

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
        Schema::dropIfExists('main_settings');
    }
};
