<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAiFieldsToDailyTaskReportsAndActivities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daily_task_reports', function (Blueprint $table) {
            $table->string('image_path', 500)->nullable();
            $table->enum('ocr_method', ['qwen_vl', 'tesseract', 'hybrid'])->default('qwen_vl')->nullable();
            $table->decimal('ai_confidence_score', 5, 4)->nullable();
            $table->json('raw_ai_response')->nullable();
            $table->json('extracted_entities')->nullable();
            $table->enum('status', ['pending', 'processing', 'ai_parsed', 'validated', 'failed', 'needs_review'])->default('pending')->nullable();
            $table->text('needs_review_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });

        Schema::table('task_activities', function (Blueprint $table) {
            $table->boolean('is_parallel')->default(false);
            $table->text('notes')->nullable();
            $table->decimal('ai_extraction_confidence', 5, 4)->nullable();
        });

        Schema::create('ai_processing_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('report_id')->nullable();
            $table->enum('ai_provider', ['qwen_vl', 'qwen_2.5', 'qwen_max']);
            $table->string('endpoint', 255)->nullable();
            $table->json('request_payload')->nullable();
            $table->json('response_payload')->nullable();
            $table->integer('status_code')->nullable();
            $table->integer('processing_time_ms')->nullable();
            $table->integer('token_usage_input')->nullable();
            $table->integer('token_usage_output')->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_processing_logs');

        Schema::table('task_activities', function (Blueprint $table) {
            $table->dropColumn(['is_parallel', 'notes', 'ai_extraction_confidence']);
        });

        Schema::table('daily_task_reports', function (Blueprint $table) {
            $table->dropColumn([
                'image_path', 'ocr_method', 'ai_confidence_score', 'raw_ai_response', 
                'extracted_entities', 'status', 'needs_review_reason', 'processed_at', 
                'reviewed_by', 'reviewed_at'
            ]);
        });
    }
}
