<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReporterFieldsToMCases extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_cases', function (Blueprint $table) {
            $table->string('reporter_name')->nullable()->after('detail');
            $table->string('reporter_contact')->nullable()->after('reporter_name');
            $table->string('ncm_customer')->nullable()->after('reporter_contact');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_cases', function (Blueprint $table) {
            $table->dropColumn(['reporter_name', 'reporter_contact', 'ncm_customer']);
        });
    }
}
