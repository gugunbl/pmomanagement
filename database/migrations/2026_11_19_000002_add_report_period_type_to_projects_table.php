<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReportPeriodTypeToProjectsTable extends Migration
{
    public function up()
    {
        Schema::table('m_project', function (Blueprint $table) {
            $table->string('project_report_period_type', 40)
                ->default('manual')
                ->after('project_report_total');
        });
    }

    public function down()
    {
        Schema::table('m_project', function (Blueprint $table) {
            $table->dropColumn('project_report_period_type');
        });
    }
}
