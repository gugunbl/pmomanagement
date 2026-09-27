<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pm1 = new App\User(["id" => 101, "name" => "PM Implementasi", "email" => "pm1@example.com", "password" => "secret123", "status" => 1]);
$project = new App\Project(["id_project" => 5, "nama_project" => "Project A", "pm_implementasi" => 10, "pm_maintenance" => 11]);
$project->setRelation('mPMImplementasi', (object)['user_id' => 101]);
$project->setRelation('mPMMaintenance', (object)['user_id' => 102]);
var_dump($project->mPMImplementasi);
var_dump($project->mPMMaintenance);
var_dump((int)$pm1->id, (int)($project->mPMImplementasi->user_id ?? 0), (int)($project->mPMMaintenance->user_id ?? 0));
var_dump((int)$pm1->id === (int)($project->mPMImplementasi->user_id ?? 0));
var_dump($pm1->canEditProject($project));
