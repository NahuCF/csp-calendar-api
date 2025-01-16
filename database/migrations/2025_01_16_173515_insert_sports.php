<?php

use App\Models\Sport;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sports = [
            'Soccer',
            'Football',
            'Basketball',
            'Volleyball',
            'Rugby',
            'Field Hockey',
            'Track and field',
            'Dodgeball',
            'Floor Hockey',
        ];

        foreach ($sports as $sport) {

            Sport::query()
                ->create([
                    'name' => $sport,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
