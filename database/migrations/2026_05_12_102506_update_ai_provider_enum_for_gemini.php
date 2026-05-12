<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateAiProviderEnumForGemini extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update ai_provider enum in ai_processing_logs to include 'gemini'
        DB::statement("ALTER TABLE ai_processing_logs MODIFY COLUMN ai_provider ENUM('qwen_vl', 'qwen_2.5', 'qwen_max', 'gemini') NOT NULL");
        
        // Update ocr_method enum in daily_task_reports to include 'gemini'
        DB::statement("ALTER TABLE daily_task_reports MODIFY COLUMN ocr_method ENUM('qwen_vl', 'tesseract', 'hybrid', 'gemini') DEFAULT 'gemini'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE ai_processing_logs MODIFY COLUMN ai_provider ENUM('qwen_vl', 'qwen_2.5', 'qwen_max') NOT NULL");
        DB::statement("ALTER TABLE daily_task_reports MODIFY COLUMN ocr_method ENUM('qwen_vl', 'tesseract', 'hybrid') DEFAULT 'qwen_vl'");
    }
}
