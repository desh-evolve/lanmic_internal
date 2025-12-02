<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;

class UserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions if they don't exist
        $permissions = [
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
        ];

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName]);
        }

        // Create or get role
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create Manori with full department permissions
        $manori = User::firstOrCreate(
            ['email' => 'manori@example.com'],
            [
                'name' => 'Manori',
                'password' => bcrypt('password'),
            ]
        );

        // Assign role
        $manori->roles()->syncWithoutDetaching([$userRole->id]);

        // Give direct permissions
        $manori->syncPermissions([
            'department.view',
            'department.create',
            'department.edit',
            'department.delete',
        ]);

        // Create Sanduni with view only permission
        $sanduni = User::firstOrCreate(
            ['email' => 'sanduni@example.com'],
            [
                'name' => 'Sanduni',
                'password' => bcrypt('password'),
            ]
        );

        // Assign role
        $sanduni->roles()->syncWithoutDetaching([$userRole->id]);

        // Give only view permission
        $sanduni->syncPermissions(['department.view']);

        $this->command->info('User permissions seeded successfully!');
    }
}