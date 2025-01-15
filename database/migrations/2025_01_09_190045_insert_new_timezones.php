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
            ['name' => 'Canada/Terranova_y_Labrador', 'deviation' => 'GMT-03:30'],
            ['name' => 'Canada/New_Brunswick', 'deviation' => 'GMT-04:00'],
            ['name' => 'Canada/Nova_Scotia', 'deviation' => 'GMT-04:00'],
            ['name' => 'Canada/Prince_Edward_Island', 'deviation' => 'GMT-04:00'],
            ['name' => 'America/Connecticut', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Delaware', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Florida', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Georgia', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Indiana', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Kentucky', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Maine', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Maryland', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Massachusetts', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Michigan', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/New_Hampshire', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/New_Jersey', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/New_York', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/North_Carolina', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Ohio', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Pennsylvania', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Rhode_Island', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Vermont', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Virginia', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/West_Virginia', 'deviation' => 'GMT-05:00'],
            ['name' => 'Canada/Ontario', 'deviation' => 'GMT-05:00'],
            ['name' => 'Canada/Quebec', 'deviation' => 'GMT-05:00'],
            ['name' => 'America/Alabama', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Arkansas', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Illinois', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Iowa', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Kansas', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Louisiana', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Minnesota', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Mississippi', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Missouri', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Nebraska', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/North_Dakota', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Oklahoma', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/South_Dakota', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Tennessee', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Texas', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Wisconsin', 'deviation' => 'GMT-06:00'],
            ['name' => 'Canada/Manitoba', 'deviation' => 'GMT-06:00'],
            ['name' => 'Canada/Nunavut', 'deviation' => 'GMT-06:00'],
            ['name' => 'Canada/Saskatchewan', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Aguascalientes', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Campeche', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Chiapas', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Coahuila', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Colima', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Durango', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Guanajuato', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Guerrero', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Hidalgo', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Jalisco', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Mexico_City', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Michoacan', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Morelos', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Nuevo_Leon', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Oaxaca', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Puebla', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Queretaro', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/San_Luis_Potosi', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Tabasco', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Tamaulipas', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Tlaxcala', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Veracruz', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Yucatan', 'deviation' => 'GMT-06:00'],
            ['name' => 'Mexico/Zacatecas', 'deviation' => 'GMT-06:00'],
            ['name' => 'America/Arizona', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/Colorado', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/Idaho', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/Montana', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/New_Mexico', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/Utah', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/Wyoming', 'deviation' => 'GMT-07:00'],
            ['name' => 'Canada/Alberta', 'deviation' => 'GMT-07:00'],
            ['name' => 'Canada/Northwest_Territories', 'deviation' => 'GMT-07:00'],
            ['name' => 'Mexico/Baja_California_Sur', 'deviation' => 'GMT-07:00'],
            ['name' => 'Mexico/Chihuahua', 'deviation' => 'GMT-07:00'],
            ['name' => 'Mexico/Nayarit', 'deviation' => 'GMT-07:00'],
            ['name' => 'Mexico/Sinaloa', 'deviation' => 'GMT-07:00'],
            ['name' => 'Mexico/Sonora', 'deviation' => 'GMT-07:00'],
            ['name' => 'America/California', 'deviation' => 'GMT-08:00'],
            ['name' => 'America/Nevada', 'deviation' => 'GMT-08:00'],
            ['name' => 'America/Oregon', 'deviation' => 'GMT-08:00'],
            ['name' => 'America/Washington', 'deviation' => 'GMT-08:00'],
            ['name' => 'Canada/British_Columbia', 'deviation' => 'GMT-08:00'],
            ['name' => 'Canada/Yukon', 'deviation' => 'GMT-08:00'],
            ['name' => 'Mexico/Baja_California', 'deviation' => 'GMT-08:00'],
            ['name' => 'America/Alaska', 'deviation' => 'GMT-09:00'],
            ['name' => 'America/Hawaii', 'deviation' => 'GMT-10:00'],
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
