<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('case_documents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('case_id');
            $table->string('document_type', 20);
            $table->string('disk', 30)->default('nextcloud');
            $table->text('path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('stored_name')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('checksum', 64)->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(false);
            $table->string('status', 20)->default('pending');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamps();

            $table->index(['case_id', 'document_type', 'is_active'], 'case_document_active_idx');
            $table->unique(['case_id', 'document_type', 'version'], 'case_document_version_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('case_documents');
    }
}
