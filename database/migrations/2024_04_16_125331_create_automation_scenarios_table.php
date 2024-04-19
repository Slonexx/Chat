<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_scenarios', function (Blueprint $table) {
            // Добавляем столбец uuid как первичный ключ
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));

            $table->uuid("automation_id");
            $table->uuid("scenario_id");
            $table->unsignedBigInteger('template_id');


            $table->foreign('scenario_id')->references('id')->on('scenario')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('templates')->onDelete('cascade');
            $table->foreign('automation_id')->references('id')->on('automations')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_scenarios');
    }
};
