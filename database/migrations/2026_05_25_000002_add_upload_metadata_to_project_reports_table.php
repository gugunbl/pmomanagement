<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadMetadataToProjectReportsTable extends Migration
{
    public function up()
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->string('uploaded_disk')->nullable()->after('uploaded_file');
            $table->string('uploaded_original_name')->nullable()->after('uploaded_disk');
        });
    }

    public function down()
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->dropColumn(['uploaded_disk', 'uploaded_original_name']);
        });
    }
}
