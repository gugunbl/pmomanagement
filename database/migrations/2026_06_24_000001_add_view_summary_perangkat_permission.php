<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddViewSummaryPerangkatPermission extends Migration
{
    public function up()
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'view-summary-perangkat', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $permissionId = DB::table('permissions')
            ->where('name', 'view-summary-perangkat')
            ->where('guard_name', 'web')
            ->value('id');

        $superAdminRoleId = DB::table('roles')
            ->where('name', 'super-admin')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId && $superAdminRoleId) {
            DB::table('role_has_permissions')->updateOrInsert([
                'permission_id' => $permissionId,
                'role_id' => $superAdminRoleId,
            ]);
        }
    }

    public function down()
    {
        $permissionId = DB::table('permissions')
            ->where('name', 'view-summary-perangkat')
            ->where('guard_name', 'web')
            ->value('id');

        if ($permissionId) {
            DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->delete();
        }

        DB::table('permissions')
            ->where('name', 'view-summary-perangkat')
            ->where('guard_name', 'web')
            ->delete();
    }
}
