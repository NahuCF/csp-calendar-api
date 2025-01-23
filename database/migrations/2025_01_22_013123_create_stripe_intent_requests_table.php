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
        Schema::create('stripe_intent_requests', function (Blueprint $table) {
            $table->id();
            $table->string('intent_id');
            $table->foreignUuid('tenant_id')->constrained();
            $table->foreignId('event_request_id')->constrained();
            $table->json('data');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_intent_requests');
    }
};
