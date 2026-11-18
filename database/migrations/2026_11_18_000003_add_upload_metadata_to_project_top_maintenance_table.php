<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUploadMetadataToProjectTopMaintenanceTable extends Migration
{
    public function up()
    {
        Schema::table('project_top_maintenance', function (Blueprint $table) {
            $table->text('uploaded_file')->nullable()->after('signers');
            $table->string('uploaded_disk', 40)->nullable()->after('uploaded_file');
            $table->text('uploaded_original_name')->nullable()->after('uploaded_disk');
            $table->timestamp('uploaded_at')->nullable()->after('uploaded_original_name');
            $table->unsignedBigInteger('uploaded_by')->nullable()->after('uploaded_at');
        });
    }

    public function down()
    {
        Schema::table('project_top_maintenance', function (Blueprint $table) {
            $table->dropColumn([
                'uploaded_file',
                'uploaded_disk',
                'uploaded_original_name',
                'uploaded_at',
                'uploaded_by',
            ]);
        });
    }
}
