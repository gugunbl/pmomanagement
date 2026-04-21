<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\User;

class SetupPerms extends Command
{
    protected $signature = 'setup:perms';
    protected $description = 'Setup permissions for interferance ratio';

    public function handle()
    {
        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        $perms = ['menu-interferance-view', 'menu-interferance-create', 'menu-interferance-edit'];
        foreach($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }
        $user = User::find(1);
        if($user) {
            $user->givePermissionTo($perms);
        }
        $role = Role::find(1);
        if($role) {
            $role->givePermissionTo($perms);
        }
        $this->info('Assigned permissions successfully');
    }
}
