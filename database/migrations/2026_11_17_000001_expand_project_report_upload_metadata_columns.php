<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->text('uploaded_file')->nullable()->change();
            $table->string('uploaded_original_name', 512)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_reports', function (Blueprint $table) {
            $table->string('uploaded_file', 255)->nullable()->change();
            $table->string('uploaded_original_name', 255)->nullable()->change();
        });
    }
};
