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
        Schema::table('event_requests', function (Blueprint $table) {
            $table->decimal('price_with_taxes', 9, 2)->default(0);
            $table->decimal('tax_amount', 9, 2)->default(0);
            $table->decimal('discount_amount', 9, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_requests', function (Blueprint $table) {
            $table->dropColumn('price_with_taxes');
            $table->dropColumn('tax_amount');
            $table->dropColumn('discount_amount');
        });
    }
};
