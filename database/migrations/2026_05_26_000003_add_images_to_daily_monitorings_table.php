<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagesToDailyMonitoringsTable extends Migration
{
    public function up()
    {
        Schema::table('daily_monitorings', function (Blueprint $table) {
            $table->json('images')->nullable()->after('status_machine');
        });
    }

    public function down()
    {
        Schema::table('daily_monitorings', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
}
