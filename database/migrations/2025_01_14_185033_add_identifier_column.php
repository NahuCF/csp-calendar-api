<?php

use App\Models\Tenant;
use App\Services\IdentifierService;
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
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('identifier')->nullable();
        });

        $tenantsWithNoIdentifier = Tenant::query()
            ->whereNull('identifier')
            ->get();

        foreach ($tenantsWithNoIdentifier as $tenant) {
            $tenant->identifier = IdentifierService::generate();
            $tenant->save();
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('identifier');
        });
    }
};
