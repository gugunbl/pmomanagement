<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyTaskReportImportsTable extends Migration
{
    public function up()
    {
        Schema::create('daily_task_report_imports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('file_path', 500);
            $table->string('status', 20)->index();
            $table->text('message')->nullable();
            $table->unsignedInteger('inserted_days')->default(0);
            $table->unsignedInteger('inserted_activities')->default(0);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_task_report_imports');
    }
}