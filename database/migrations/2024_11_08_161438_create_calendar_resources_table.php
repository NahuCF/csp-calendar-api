<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calendar_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('calendar_resource_type_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignUuid('tenant_id')->references('tenant_id')->on('users');
            $table->foreignId('facility_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_resources');
    }
};
