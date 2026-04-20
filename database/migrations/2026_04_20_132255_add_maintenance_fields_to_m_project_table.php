<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMaintenanceFieldsToMProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_project', function (Blueprint $table) {
            $table->string('tipe_maintenance')->nullable()->after('status_maintenance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_project', function (Blueprint $table) {
            $table->dropColumn(['tipe_maintenance']);
        });
    }
}
