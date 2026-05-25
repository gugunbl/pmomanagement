<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReportStatusLogsTable extends Migration
{
    public function up()
    {
        Schema::create('project_report_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->unsignedBigInteger('user_id');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamps();

            $table->index('project_report_id');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_report_status_logs');
    }
}
