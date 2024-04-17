<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            // Добавляем столбец uuid как первичный ключ
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));
            $table->string("accountId", 64);

            $table->boolean("is_default");
            $table->string("line", 64);
            $table->string("messenger", 64);
            $table->unsignedBigInteger("employee_id");


            $table->foreign('employee_id')->references('id')->on('employee_models')->onDelete('cascade');
            $table->foreign('accountId')->references('accountId')->on('setting_models')->onDelete('cascade');

            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('automations');
    }
};
