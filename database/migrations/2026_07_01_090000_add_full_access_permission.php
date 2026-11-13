<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Introduces the 'full-access' permission that replaces the hardcoded
 * hasRole('admin') super-pass throughout the app. Grants it to whichever
 * role currently acts as administrator so existing installs keep working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('permissions')->where('name', 'full-access')->exists()) {
            return;
        }

        // Roles that should receive full-access, identified WITHOUT trusting a
        // role name alone:
        //   (a) any role literally named 'admin' (historical super-user), and
        //   (b) any role that already holds every existing permission.
        $totalPermissions = DB::table('permissions')->count();

        $rolesWithAllPerms = DB::table('permission_role')
            ->select('role_id')
            ->groupBy('role_id')
            ->havingRaw('COUNT(DISTINCT permission_id) >= ?', [$totalPermissions])
            ->pluck('role_id');

        $adminRoleIds = DB::table('roles')->where('name', 'admin')->pluck('id');

        $targetRoleIds = $rolesWithAllPerms->merge($adminRoleIds)->unique();

        // Create the permission.
        $permissionId = DB::table('permissions')->insertGetId([
            'name'        => 'full-access',
            'module'      => 'system',
            'description' => 'Full access — bypasses all permission checks',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        foreach ($targetRoleIds as $roleId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $permissionId,
                'role_id'       => $roleId,
            ]);
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', 'full-access')->first();
        if ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
