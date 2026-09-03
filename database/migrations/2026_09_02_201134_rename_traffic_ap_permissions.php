<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\User;

class RenameTrafficApPermissions extends Migration
{
    /**
     * Mapping: nama permission lama => nama baru sesuai permintaan
     */
    protected $permissionMap = [
        'menu-traffic-ap-view'    => 'menu_monitoring_ap',
        'menu-traffic-ap-mapping' => 'sub_mapping_lantai_ap',
        'menu-traffic-ap-input'   => 'sub_traffic_ap',
        'menu-traffic-ap-monitor' => 'sub_monitor_ap',
    ];

    public function up()
    {
        // 1. Tambahkan permission baru (jika belum ada)
        foreach ($this->permissionMap as $newName) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $newName, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            $oldPermissions = DB::table('permissions')
                ->whereIn('name', array_keys($this->permissionMap))
                ->where('guard_name', 'web')
                ->pluck('id', 'name');

            $newPermissions = DB::table('permissions')
                ->whereIn('name', array_values($this->permissionMap))
                ->where('guard_name', 'web')
                ->pluck('id', 'name');

            // 2. Salin role_has_permissions: role yang punya permission lama, dapatkan permission baru
            foreach ($this->permissionMap as $oldName => $newName) {
                $oldPermId = $oldPermissions[$oldName] ?? null;
                $newPermId = $newPermissions[$newName] ?? null;

                if (!$oldPermId || !$newPermId) continue;

                // Ambil semua role_id yang punya permission lama
                $roleIds = DB::table('role_has_permissions')
                    ->where('permission_id', $oldPermId)
                    ->pluck('role_id');

                foreach ($roleIds as $roleId) {
                    DB::table('role_has_permissions')->updateOrInsert(
                        ['permission_id' => $newPermId, 'role_id' => $roleId],
                        []
                    );
                }

                // Salin model_has_permissions (permission per user)
                $userRows = DB::table('model_has_permissions')
                    ->where('permission_id', $oldPermId)
                    ->where('model_type', 'App\\User')
                    ->pluck('model_id');

                foreach ($userRows as $userId) {
                    DB::table('model_has_permissions')->updateOrInsert(
                        [
                            'permission_id' => $newPermId,
                            'model_type'    => 'App\\User',
                            'model_id'      => $userId,
                        ],
                        []
                    );
                }
            }

            // 3. Pastikan semua role mendapatkan permission baru
            $newPermissionNames = array_values($this->permissionMap);
            $allRoles = Role::all();
            foreach ($allRoles as $role) {
                $role->givePermissionTo($newPermissionNames);
            }

            // 4. Pastikan user pertama (super-admin) juga punya
            $user = User::find(1);
            if ($user) {
                $user->givePermissionTo($newPermissionNames);
            }

            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        } catch (\Exception $e) {
            \Log::warning('RenameTrafficApPermissions migration: ' . $e->getMessage());
        }
    }

    public function down()
    {
        // Hanya hapus permission baru; permission lama tetap ada
        DB::table('permissions')
            ->whereIn('name', array_values($this->permissionMap))
            ->where('guard_name', 'web')
            ->delete();
    }
}

