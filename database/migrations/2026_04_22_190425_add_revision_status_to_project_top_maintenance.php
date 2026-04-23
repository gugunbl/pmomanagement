<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRevisionStatusToProjectTopMaintenance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE project_top_maintenance MODIFY COLUMN status ENUM('planned', 'submitted', 'revision', 'paid') DEFAULT 'planned'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE project_top_maintenance MODIFY COLUMN status ENUM('planned', 'submitted', 'paid') DEFAULT 'planned'");
    }
}
