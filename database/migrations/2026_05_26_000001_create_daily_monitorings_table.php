<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDailyMonitoringsTable extends Migration
{
    public function up()
    {
        Schema::create('daily_monitorings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('id_project');
            $table->string('site_environment_name')->nullable();
            $table->string('report_name')->default('Report Daily Monitoring');
            $table->dateTime('report_datetime');
            $table->unsignedInteger('total_server')->default(0);
            $table->unsignedInteger('server_on')->default(0);
            $table->unsignedInteger('offline')->default(0);
            $table->unsignedInteger('total_server_off')->default(0);
            $table->enum('health_indicator', ['normal', 'warning'])->default('normal');
            $table->unsignedInteger('server_warning')->default(0);
            $table->text('alert_status')->nullable();
            $table->enum('status', ['healthy', 'not_healthy', 'warning', 'ok'])->default('healthy');
            $table->unsignedInteger('farm')->default(0);
            $table->unsignedInteger('total_machine_farm')->default(0);
            $table->string('status_machine')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->index('id_project');
            $table->index('report_datetime');
            $table->index('status');
            $table->index('health_indicator');
        });
    }

    public function down()
    {
        Schema::dropIfExists('daily_monitorings');
    }
}
