<?php

use App\Models\CalendarResourceType;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        CalendarResourceType::query()
            ->insert([
                ['name' => 'Track'],
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
