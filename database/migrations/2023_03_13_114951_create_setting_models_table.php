<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('setting_models', function (Blueprint $table) {
            $table->string('accountId')->unique()->primary();
            $table->string('tokenMs');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_models');
    }
};
