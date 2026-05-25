<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadedFileToProjectReportsTable extends Migration
{
    public function up()
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->string('uploaded_file')->nullable()->after('keterangan');
        });
    }

    public function down()
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->dropColumn('uploaded_file');
        });
    }
}
