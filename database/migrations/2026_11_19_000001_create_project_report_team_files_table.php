<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReportTeamFilesTable extends Migration
{
    public function up()
    {
        Schema::create('project_report_team_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->unsignedBigInteger('team_support_id');
            $table->text('uploaded_file')->nullable();
            $table->string('uploaded_disk', 40)->nullable();
            $table->text('uploaded_original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->unique(['project_report_id', 'team_support_id'], 'uq_report_team_file');
            $table->index('team_support_id');
            $table->foreign('project_report_id')
                ->references('id')
                ->on('project_reports')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_report_team_files');
    }
}
