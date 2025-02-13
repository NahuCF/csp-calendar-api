<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'Edit Timezone']);

        $admin = Role::where('name', 'Admin')->first();
        $admin->givePermissionTo($permission);

        $adminUsers = User::role('Admin')->get();
        foreach ($adminUsers as $user) {
            $user->givePermissionTo($permission);
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
