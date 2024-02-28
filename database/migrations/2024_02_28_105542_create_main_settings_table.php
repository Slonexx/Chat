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
            $table->string("app_id", 20);
            $table->string("login", 255);
            $table->string("password", 255);

            //$table->string("token", 128)->nullable();
            $table->boolean("is_activate");

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
