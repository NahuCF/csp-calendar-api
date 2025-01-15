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
        Schema::create('event_request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_request_id')->constrained();
            $table->foreignUuid('tenant_id');
            $table->foreignId('user_id')->constrained();
            $table->foreignId('calendar_resource_id')->constrained();
            $table->decimal('price', 15, 2);
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_request_details');
    }
};
