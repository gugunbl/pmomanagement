<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReportNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('project_report_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('project_report_id');
            $table->string('notification_type', 50)->default('period_end');
            $table->date('trigger_date');
            $table->text('recipient_snapshot')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->unique(
                ['project_report_id', 'notification_type', 'trigger_date'],
                'project_report_notification_unique'
            );
            $table->index(['status', 'trigger_date'], 'project_report_notification_status_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('project_report_notifications');
    }
}
