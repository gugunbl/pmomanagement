<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSikHostnameToMPerangkatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_perangkat', function (Blueprint $table) {
            $table->string('sik')->nullable()->after('serialnumber');
            $table->string('hostname')->nullable()->after('sik');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_perangkat', function (Blueprint $table) {
            $table->dropColumn(['sik', 'hostname']);
        });
    }
}
