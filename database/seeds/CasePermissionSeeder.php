<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CasePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // New permissions for Case Management
        $permissions = [
            'view-case',
            'create-case',
            'edit-case',
            'delete-case',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Assign new permissions to super-admin
        $admin = Role::findByName('super-admin');
        $admin->givePermissionTo($permissions);

        // Create Case Manager role and assign permissions
        $role = Role::findOrCreate('Case Manager');
        $role->syncPermissions([
            'view-case',
            'create-case',
            'edit-case',
        ]);
        
        $this->command->info('Case Management permissions and role created successfully.');
    }
}
