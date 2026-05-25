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
        if (Schema::hasTable('daily_task_reports')) {
            Schema::table('daily_task_reports', function (Blueprint $table) {
                if (!Schema::hasColumn('daily_task_reports', 'image_path')) {
                    $table->string('image_path', 500)->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'ocr_method')) {
                    $table->enum('ocr_method', ['qwen_vl', 'tesseract', 'hybrid'])->default('qwen_vl')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'ai_confidence_score')) {
                    $table->decimal('ai_confidence_score', 5, 4)->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'raw_ai_response')) {
                    $table->json('raw_ai_response')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'extracted_entities')) {
                    $table->json('extracted_entities')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'status')) {
                    $table->enum('status', ['pending', 'processing', 'ai_parsed', 'validated', 'failed', 'needs_review'])->default('pending')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'needs_review_reason')) {
                    $table->text('needs_review_reason')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'processed_at')) {
                    $table->timestamp('processed_at')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'reviewed_by')) {
                    $table->unsignedBigInteger('reviewed_by')->nullable();
                }
                if (!Schema::hasColumn('daily_task_reports', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('task_activities')) {
            Schema::table('task_activities', function (Blueprint $table) {
                if (!Schema::hasColumn('task_activities', 'is_parallel')) {
                    $table->boolean('is_parallel')->default(false);
                }
                if (!Schema::hasColumn('task_activities', 'notes')) {
                    $table->text('notes')->nullable();
                }
                if (!Schema::hasColumn('task_activities', 'ai_extraction_confidence')) {
                    $table->decimal('ai_extraction_confidence', 5, 4)->nullable();
                }
            });
        }

        if (!Schema::hasTable('ai_processing_logs')) {
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ai_processing_logs');

        if (Schema::hasTable('task_activities')) {
            $columns = array_filter(['is_parallel', 'notes', 'ai_extraction_confidence'], function ($column) {
                return Schema::hasColumn('task_activities', $column);
            });

            if (!empty($columns)) {
                Schema::table('task_activities', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }

        if (Schema::hasTable('daily_task_reports')) {
            $columns = array_filter([
                'image_path', 'ocr_method', 'ai_confidence_score', 'raw_ai_response',
                'extracted_entities', 'status', 'needs_review_reason', 'processed_at',
                'reviewed_by', 'reviewed_at',
            ], function ($column) {
                return Schema::hasColumn('daily_task_reports', $column);
            });

            if (!empty($columns)) {
                Schema::table('daily_task_reports', function (Blueprint $table) use ($columns) {
                    $table->dropColumn($columns);
                });
            }
        }
    }
}
