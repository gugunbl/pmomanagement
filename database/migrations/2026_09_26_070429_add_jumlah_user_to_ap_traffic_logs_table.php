<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddJumlahUserToApTrafficLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ap_traffic_logs', function (Blueprint $table) {
            $table->integer('jumlah_user')->default(0)->nullable()->after('traffic_unit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ap_traffic_logs', function (Blueprint $table) {
            $table->dropColumn('jumlah_user');
        });
    }
}
