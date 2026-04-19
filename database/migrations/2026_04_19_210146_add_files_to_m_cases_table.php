<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFilesToMCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_cases', function (Blueprint $table) {
            $table->string('evidence_photo')->nullable()->after('resolution_detail');
            $table->string('rca_file')->nullable()->after('evidence_photo');
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
            $table->dropColumn(['evidence_photo', 'rca_file']);
        });
    }
}
