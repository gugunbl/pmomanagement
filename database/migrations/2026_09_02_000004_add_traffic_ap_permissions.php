<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

class AddTrafficApPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $permissions = [
            'menu-traffic-ap-view',
            'menu-traffic-ap-mapping',
            'menu-traffic-ap-input',
            'menu-traffic-ap-monitor',
        ];

        foreach ($permissions as $p) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $p, 'guard_name' => 'web'],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        try {
            app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

            // Grant to Super Admin / Admin role (id: 1 or name: super-admin/admin)
            $roles = Role::whereIn('id', [1, 2])->orWhereIn('name', ['super-admin', 'admin', 'Super Admin', 'Admin'])->get();
            foreach ($roles as $role) {
                $role->givePermissionTo($permissions);
            }

            // Grant to user 1 (first admin)
            $user = User::find(1);
            if ($user) {
                $user->givePermissionTo($permissions);
            }
        } catch (\Exception $e) {
            // Ignore if role/user table is empty during testing
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $permissions = [
            'menu-traffic-ap-view',
            'menu-traffic-ap-mapping',
            'menu-traffic-ap-input',
            'menu-traffic-ap-monitor',
        ];

        DB::table('permissions')
            ->whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->delete();
    }
}
