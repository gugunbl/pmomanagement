<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectTopMaintenanceStatusLogsTable extends Migration
{
    public function up()
    {
        Schema::create('project_top_maintenance_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_top_maintenance_id');
            $table->unsignedBigInteger('user_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamps();

            $table->foreign('project_top_maintenance_id', 'fk_ptm_status_logs_ptm_id')
                  ->references('id')
                  ->on('project_top_maintenance')
                  ->onDelete('cascade');
            $table->foreign('user_id', 'fk_ptm_status_logs_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_top_maintenance_status_logs');
    }
}
