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
        $admin = Role::create(['name' => 'admin', 'description' => 'Administrator with full access', 'status' => 'active']);
        $manager = Role::create(['name' => 'manager', 'description' => 'Manager with limited administrative access', 'status' => 'active']);
        $user = Role::create(['name' => 'user', 'description' => 'Regular User', 'status' => 'active']);

        // Create permissions organized by module
        $permissions = [
            // Dashboard Module
            ['name' => 'view-dashboard', 'module' => 'dashboard', 'description' => 'View dashboard'],

            // User Management Module
            ['name' => 'view-users', 'module' => 'users', 'description' => 'View users list'],
            ['name' => 'create-users', 'module' => 'users', 'description' => 'Create new users'],
            ['name' => 'edit-users', 'module' => 'users', 'description' => 'Edit existing users'],
            ['name' => 'delete-users', 'module' => 'users', 'description' => 'Delete users'],
            ['name' => 'assign-user-permissions', 'module' => 'users', 'description' => 'Assign permissions to users'],

            // Role Management Module
            ['name' => 'view-roles', 'module' => 'roles', 'description' => 'View roles list'],
            ['name' => 'create-roles', 'module' => 'roles', 'description' => 'Create new roles'],
            ['name' => 'edit-roles', 'module' => 'roles', 'description' => 'Edit existing roles'],
            ['name' => 'delete-roles', 'module' => 'roles', 'description' => 'Delete roles'],

            // Permission Management Module
            ['name' => 'view-permissions', 'module' => 'permissions', 'description' => 'View permissions list'],
            ['name' => 'create-permissions', 'module' => 'permissions', 'description' => 'Create new permissions'],
            ['name' => 'edit-permissions', 'module' => 'permissions', 'description' => 'Edit existing permissions'],
            ['name' => 'delete-permissions', 'module' => 'permissions', 'description' => 'Delete permissions'],

            // Department Management Module
            ['name' => 'view-departments', 'module' => 'departments', 'description' => 'View departments list'],
            ['name' => 'create-departments', 'module' => 'departments', 'description' => 'Create new departments'],
            ['name' => 'edit-departments', 'module' => 'departments', 'description' => 'Edit existing departments'],
            ['name' => 'delete-departments', 'module' => 'departments', 'description' => 'Delete departments'],

            // Sub-Department Management Module
            ['name' => 'view-sub-departments', 'module' => 'sub-departments', 'description' => 'View sub-departments list'],
            ['name' => 'create-sub-departments', 'module' => 'sub-departments', 'description' => 'Create new sub-departments'],
            ['name' => 'edit-sub-departments', 'module' => 'sub-departments', 'description' => 'Edit existing sub-departments'],
            ['name' => 'delete-sub-departments', 'module' => 'sub-departments', 'description' => 'Delete sub-departments'],

            // Division Management Module
            ['name' => 'view-divisions', 'module' => 'divisions', 'description' => 'View divisions list'],
            ['name' => 'create-divisions', 'module' => 'divisions', 'description' => 'Create new divisions'],
            ['name' => 'edit-divisions', 'module' => 'divisions', 'description' => 'Edit existing divisions'],
            ['name' => 'delete-divisions', 'module' => 'divisions', 'description' => 'Delete divisions'],

            // Requisition Module
            ['name' => 'view-requisitions', 'module' => 'requisitions', 'description' => 'View requisitions'],
            ['name' => 'create-requisitions', 'module' => 'requisitions', 'description' => 'Create requisitions'],
            ['name' => 'edit-requisitions', 'module' => 'requisitions', 'description' => 'Edit requisitions'],
            ['name' => 'delete-requisitions', 'module' => 'requisitions', 'description' => 'Delete requisitions'],
            ['name' => 'approve-requisitions', 'module' => 'requisitions', 'description' => 'Approve/reject requisitions'],
            ['name' => 'issue-requisitions', 'module' => 'requisitions', 'description' => 'Issue items for requisitions'],

            // Purchase Order Module
            ['name' => 'view-purchase-orders', 'module' => 'purchase-orders', 'description' => 'View purchase orders'],
            ['name' => 'clear-purchase-orders', 'module' => 'purchase-orders', 'description' => 'Clear purchase orders'],

            // Returns Module
            ['name' => 'view-returns', 'module' => 'returns', 'description' => 'View returns'],
            ['name' => 'create-returns', 'module' => 'returns', 'description' => 'Create returns'],
            ['name' => 'edit-returns', 'module' => 'returns', 'description' => 'Edit returns'],
            ['name' => 'delete-returns', 'module' => 'returns', 'description' => 'Delete returns'],
            ['name' => 'approve-returns', 'module' => 'returns', 'description' => 'Approve return items (GRN/Scrap)'],

            // Reports Module
            ['name' => 'view-reports', 'module' => 'reports', 'description' => 'View reports'],
            ['name' => 'export-reports', 'module' => 'reports', 'description' => 'Export reports'],

            // Settings Module
            ['name' => 'view-settings', 'module' => 'settings', 'description' => 'View system settings'],
            ['name' => 'edit-settings', 'module' => 'settings', 'description' => 'Edit system settings'],
        ];

        $permissionModels = [];
        foreach ($permissions as $permission) {
            $permissionModels[] = Permission::create([
                'name' => $permission['name'],
                'module' => $permission['module'],
                'description' => $permission['description'],
                'status' => 'active',
            ]);
        }

        // Assign all permissions to admin role
        $admin->permissions()->attach(Permission::all());

        // Assign permissions to manager role
        $managerPermissions = Permission::whereIn('name', [
            'view-dashboard',

            // Department Management
            'view-departments',
            'create-departments',
            'edit-departments',

            // Sub-Department Management
            'view-sub-departments',
            'create-sub-departments',
            'edit-sub-departments',

            // Division Management
            'view-divisions',
            'create-divisions',
            'edit-divisions',

            // Requisition Management
            'view-requisitions',
            'approve-requisitions',
            'issue-requisitions',

            // Purchase Orders
            'view-purchase-orders',
            'clear-purchase-orders',

            // Returns
            'view-returns',
            'approve-returns',

            // Reports
            'view-reports',
            'export-reports',
        ])->get();
        $manager->permissions()->attach($managerPermissions);

        // Assign basic permissions to user role
        $userPermissions = Permission::whereIn('name', [
            'view-dashboard',
            'view-requisitions',
            'create-requisitions',
            'edit-requisitions',
            'view-returns',
            'create-returns',
            'edit-returns',
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

        // Example: Assign a direct permission to a specific user (optional)
        // This demonstrates the permission_user table functionality
        //$specificPermission = Permission::where('name', 'view-settings')->first();
        //if ($specificPermission) {
        //    $adminUser->permissions()->attach($specificPermission);
        //}

        // Assign ALL permissions directly to adminUser
        $adminUser->permissions()->attach(Permission::all());

        $this->command->info('Roles, permissions, and users seeded successfully!');
        $this->command->info('Admin: admin@lanmic.com / password');
        $this->command->info('Manager: manager@lanmic.com / password (has view-settings via direct permission)');
        $this->command->info('User: user@lanmic.com / password');
    }
}
