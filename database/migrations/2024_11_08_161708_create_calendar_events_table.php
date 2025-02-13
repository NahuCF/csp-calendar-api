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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('client_id')->constrained();
            $table->foreignId('calendar_resource_id')->constrained();
            $table->bigInteger('user_id');
            $table->foreignUuid('tenant_id');
            $table->boolean('is_paid')->default(0);
            $table->decimal('price', 15, 2)->nullable()->default(null);
            $table->decimal('discount', 15, 2)->nullable()->default(null);
            $table->decimal('discount_percentage', 15, 2)->nullable()->default(null);
            $table->boolean('will_assist')->nullable()->default(null);
            $table->foreignId('category_id')->constrained();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
