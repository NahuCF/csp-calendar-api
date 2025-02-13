<?php

use App\Models\CountrySubdivision;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private $subdivisions = [

    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        CountrySubdivision::query()
            ->insert([]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
