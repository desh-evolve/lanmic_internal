<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $admin = Role::create(['name' => 'admin', 'description' => 'Administrator with full access']);
        $manager = Role::create(['name' => 'manager', 'description' => 'Manager with limited administrative access']);
        $user = Role::create(['name' => 'user', 'description' => 'Regular User']);

        // Create permissions
        $permissions = [
            // Dashboard
            ['name' => 'view-dashboard', 'description' => 'View dashboard'],
            
            // User Management
            ['name' => 'view-users', 'description' => 'View users list'],
            ['name' => 'create-users', 'description' => 'Create new users'],
            ['name' => 'edit-users', 'description' => 'Edit existing users'],
            ['name' => 'delete-users', 'description' => 'Delete users'],
            
            // Role Management
            ['name' => 'view-roles', 'description' => 'View roles list'],
            ['name' => 'create-roles', 'description' => 'Create new roles'],
            ['name' => 'edit-roles', 'description' => 'Edit existing roles'],
            ['name' => 'delete-roles', 'description' => 'Delete roles'],
            
            // Permission Management
            ['name' => 'view-permissions', 'description' => 'View permissions list'],
            ['name' => 'create-permissions', 'description' => 'Create new permissions'],
            ['name' => 'edit-permissions', 'description' => 'Edit existing permissions'],
            ['name' => 'delete-permissions', 'description' => 'Delete permissions'],
            
            // Reports
            ['name' => 'view-reports', 'description' => 'View reports'],
            ['name' => 'create-reports', 'description' => 'Create reports'],
            ['name' => 'export-reports', 'description' => 'Export reports'],
            
            // Settings
            ['name' => 'view-settings', 'description' => 'View system settings'],
            ['name' => 'edit-settings', 'description' => 'Edit system settings'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Assign all permissions to admin
        $admin->permissions()->attach(Permission::all());

        // Assign limited permissions to manager
        $managerPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-users',
            'create-users',
            'edit-users',
            'view-reports',
            'create-reports',
            'export-reports',
        ])->get();
        $manager->permissions()->attach($managerPermissions);

        // Assign basic permissions to user
        $userPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-reports',
        ])->get();
        $user->permissions()->attach($userPermissions);

        // Create admin user
        $adminUser = User::create([
            'name' => 'Admin User',
            'email' => 'admin@lanmic.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($admin);

        // Create manager user
        $managerUser = User::create([
            'name' => 'Manager User',
            'email' => 'manager@lanmic.com',
            'password' => Hash::make('password'),
        ]);
        $managerUser->roles()->attach($manager);

        // Create regular user
        $regularUser = User::create([
            'name' => 'Regular User',
            'email' => 'user@lanmic.com',
            'password' => Hash::make('password'),
        ]);
        $regularUser->roles()->attach($user);
    }
}