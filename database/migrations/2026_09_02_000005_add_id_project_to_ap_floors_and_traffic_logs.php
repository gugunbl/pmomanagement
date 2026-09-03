<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdProjectToApFloorsAndTrafficLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ap_floors', function (Blueprint $table) {
            if (!Schema::hasColumn('ap_floors', 'id_project')) {
                $table->unsignedBigInteger('id_project')->nullable()->after('id');
                $table->index('id_project');
            }
        });

        Schema::table('ap_traffic_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('ap_traffic_logs', 'id_project')) {
                $table->unsignedBigInteger('id_project')->nullable()->after('id');
                $table->index('id_project');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ap_floors', function (Blueprint $table) {
            if (Schema::hasColumn('ap_floors', 'id_project')) {
                $table->dropIndex(['id_project']);
                $table->dropColumn('id_project');
            }
        });

        Schema::table('ap_traffic_logs', function (Blueprint $table) {
            if (Schema::hasColumn('ap_traffic_logs', 'id_project')) {
                $table->dropIndex(['id_project']);
                $table->dropColumn('id_project');
            }
        });
    }
}
