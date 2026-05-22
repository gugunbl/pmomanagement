<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddExcelToOcrMethodEnum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE daily_task_reports MODIFY COLUMN ocr_method ENUM('qwen_vl', 'tesseract', 'hybrid', 'gemini', 'excel') DEFAULT 'gemini'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE daily_task_reports MODIFY COLUMN ocr_method ENUM('qwen_vl', 'tesseract', 'hybrid', 'gemini') DEFAULT 'gemini'");
    }
}
