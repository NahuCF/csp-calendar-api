<?php

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
        $roleAdmin = Role::create(['name' => '-Calendar- Admin']);
        $roleViewer = Role::create(['name' => '-Calendar- Viewer']);
        $bookingManager = Role::create(['name' => '-Calendar- Booking Manager']);

        $permissionView = Permission::create(['name' => 'View Calendar']);
        $permissionEdit = Permission::create(['name' => 'Edit Calendar']);
        $permissionCreate = Permission::create(['name' => 'Create Reservation']);
        $permissionDelete = Permission::create(['name' => 'Delete Reservation']);
        $permissionCreateUsers = Permission::create(['name' => 'Create Users']);
        $permissionCreateResources = Permission::create(['name' => 'Create Resources']);

        $roleAdmin->syncPermissions([$permissionView, $permissionEdit, $permissionCreate, $permissionDelete, $permissionCreateUsers, $permissionCreateResources]);
        $roleViewer->syncPermissions([$permissionView]);
        $bookingManager->syncPermissions([$permissionView, $permissionCreate, $permissionEdit, $permissionDelete, $permissionCreateResources]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
