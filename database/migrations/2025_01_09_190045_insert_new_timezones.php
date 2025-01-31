<?php

use App\Models\Timezone;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('timezones')->update([
            'show' => false,
        ]);

        $timezones = [
            ['name' => 'Canada/Toronto', 'deviation' => 'GMT-05:00'],
            ['name' => 'Canada/Winnipeg', 'deviation' => 'GMT-06:00'],
            ['name' => 'Canada/Calgary', 'deviation' => 'GMT-07:00'],
            ['name' => 'Canada/Vancouver', 'deviation' => 'GMT-08:00'],
        ];

        Timezone::insert($timezones);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
