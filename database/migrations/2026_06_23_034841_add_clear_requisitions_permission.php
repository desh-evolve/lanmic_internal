<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('permissions')->where('name', 'clear-requisitions')->exists()) {
            return;
        }

        $permissionId = DB::table('permissions')->insertGetId([
            'name'        => 'clear-requisitions',
            'module'      => 'requisitions',
            'description' => 'Manually force-clear requisition status',
            'status'      => 'active',
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        // Grant to every role that already has 'approve-requisitions'
        $approvePermId = DB::table('permissions')
            ->where('name', 'approve-requisitions')
            ->value('id');

        if ($approvePermId) {
            $roleIds = DB::table('permission_role')
                ->where('permission_id', $approvePermId)
                ->pluck('role_id');

            foreach ($roleIds as $roleId) {
                DB::table('permission_role')->insertOrIgnore([
                    'permission_id' => $permissionId,
                    'role_id'       => $roleId,
                ]);
            }
        }
    }

    public function down(): void
    {
        $permission = DB::table('permissions')->where('name', 'clear-requisitions')->first();
        if ($permission) {
            DB::table('permission_role')->where('permission_id', $permission->id)->delete();
            DB::table('permissions')->where('id', $permission->id)->delete();
        }
    }
};
