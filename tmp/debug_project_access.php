<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pm1 = new App\User(["id" => 101, "name" => "PM Implementasi", "email" => "pm1@example.com", "password" => "secret123", "status" => 1]);
$pm2 = new App\User(["id" => 102, "name" => "PM Maintenance", "email" => "pm2@example.com", "password" => "secret123", "status" => 1]);
$other = new App\User(["id" => 999, "name" => "User Biasa", "email" => "user@example.com", "password" => "secret123", "status" => 1]);
$sa = new App\User(["id" => 200, "name" => "Admin", "email" => "admin@example.com", "password" => "secret123", "status" => 1]);
$sa->setRelation('roles', collect([(object)['name' => 'super-admin']]));

$project = new App\Project(["id_project" => 5, "nama_project" => "Project A", "pm_implementasi" => 10, "pm_maintenance" => 11]);
$project->setRelation('mPMImplementasi', (object)['user_id' => 101]);
$project->setRelation('mPMMaintenance', (object)['user_id' => 102]);

var_dump([
    'pm1' => $pm1->canEditProject($project),
    'pm2' => $pm2->canEditProject($project),
    'sa' => $sa->canEditProject($project),
    'other' => $other->canEditProject($project),
    'superadmin_detected' => $sa->isSuperAdmin(),
    'pm1_role' => method_exists($pm1, 'hasRole') ? $pm1->hasRole('super-admin') : 'no method',
    'sa_roles_loaded' => $sa->relationLoaded('roles'),
    'sa_roles_names' => $sa->roles->pluck('name')->all(),
]);
