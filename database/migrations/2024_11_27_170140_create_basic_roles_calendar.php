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
        $roleAdmin = Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $roleViewer = Role::create(['name' => 'Viewer', 'guard_name' => 'web']);
        $bookingManager = Role::create(['name' => 'Booking Manager', 'guard_name' => 'web']);

        $permissionView = Permission::create(['name' => 'View Calendar', 'guard_name' => 'web']);
        $permissionEdit = Permission::create(['name' => 'Edit Calendar', 'guard_name' => 'web']);
        $permissionCreate = Permission::create(['name' => 'Create Reservation', 'guard_name' => 'web']);
        $permissionDelete = Permission::create(['name' => 'Delete Reservation', 'guard_name' => 'web']);
        $permissionCreateUsers = Permission::create(['name' => 'Create Users', 'guard_name' => 'web']);
        $permissionCreateResources = Permission::create(['name' => 'Create Resources', 'guard_name' => 'web']);

        $roleAdmin->syncPermissions([$permissionView, $permissionEdit, $permissionCreate, $permissionDelete, $permissionCreateUsers, $permissionCreateResources]);
        $roleViewer->syncPermissions([$permissionView]);
        $bookingManager->syncPermissions([$permissionView, $permissionCreate, $permissionEdit, $permissionDelete, $permissionCreateResources]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
