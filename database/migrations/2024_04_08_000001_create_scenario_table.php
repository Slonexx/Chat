<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scenario', function (Blueprint $table) {
            // Добавляем столбец uuid как первичный ключ
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string("accountId", 64);

            $table->string("entity", 64);
            $table->uuid("status");
            $table->uuid("channel")->nullable();
            $table->uuid("project")->nullable();


            $table->unsignedBigInteger('template_id')->nullable();
            $table->foreign('template_id')->references('id')->on('templates');


            $table->foreign('accountId')->references('accountId')->on('setting_models')->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scenario');
    }
};
