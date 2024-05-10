<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();

            $table->uuid('accountId');
            $table->boolean('is_activity_agent');
            $table->boolean('notes')->nullable();
            $table->boolean('is_messenger');
            $table->timestamp('last_start')->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
