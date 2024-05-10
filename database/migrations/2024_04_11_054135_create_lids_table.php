<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('lids', function (Blueprint $table) {
            $table->id();
            $table->uuid("accountId");
            $table->boolean("is_activity_settings");
            $table->boolean("is_activity_order");
            $table->uuid("lid");
            $table->string("responsible", 2);
            $table->uuid("responsible_uuid", 2)->nullable();
            $table->boolean("tasks",)->nullable();

            $table->uuid("organization", 36)->nullable();
            $table->uuid("organization_account", 36)->nullable();
            $table->uuid("states", 36)->nullable();
            $table->uuid("project_uid", 36)->nullable();
            $table->uuid("sales_channel_uid", 36)->nullable();
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('lids');
    }
};
