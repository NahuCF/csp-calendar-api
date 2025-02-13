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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_number');
            $table->integer('number');
            $table->foreignUuid('tenant_id')->constrained();
            $table->timestamp('start_at');
            $table->timestamp('end_at');
            $table->decimal('price', 15, 2);
            $table->decimal('discount', 15, 2);
            $table->string('sport_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
