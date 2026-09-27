<?php

namespace Tests\Feature;

use App\Project;
use App\User;
use Tests\TestCase;

class ProjectEditAccessTest extends TestCase
{
    public function test_only_assigned_pm_and_super_admin_can_edit_project(): void
    {
        $pmImplementasiUser = new User([
            'name' => 'PM Implementasi',
            'email' => 'pm1@example.com',
            'password' => 'secret123',
            'status' => 1,
        ]);
        $pmImplementasiUser->forceFill(['id' => 101]);

        $pmMaintenanceUser = new User([
            'name' => 'PM Maintenance',
            'email' => 'pm2@example.com',
            'password' => 'secret123',
            'status' => 1,
        ]);
        $pmMaintenanceUser->forceFill(['id' => 102]);

        $otherUser = new User([
            'name' => 'User Biasa',
            'email' => 'user@example.com',
            'password' => 'secret123',
            'status' => 1,
        ]);
        $otherUser->forceFill(['id' => 999]);

        $superAdmin = new User([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret123',
            'status' => 1,
        ]);
        $superAdmin->forceFill(['id' => 200]);
        $superAdmin->setRelation('roles', collect([(object) ['name' => 'super-admin']]));

        $project = new Project([
            'id_project' => 5,
            'nama_project' => 'Project A',
            'pm_implementasi' => 10,
            'pm_maintenance' => 11,
        ]);
        $project->setRelation('mPMImplementasi', (object) ['user_id' => 101]);
        $project->setRelation('mPMMaintenance', (object) ['user_id' => 102]);

        $this->assertTrue($pmImplementasiUser->canEditProject($project));
        $this->assertTrue($pmMaintenanceUser->canEditProject($project));
        $this->assertTrue($superAdmin->canEditProject($project));
        $this->assertFalse($otherUser->canEditProject($project));
    }
}
