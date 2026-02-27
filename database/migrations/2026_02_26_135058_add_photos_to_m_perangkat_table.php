<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhotosToMPerangkatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_perangkat', function (Blueprint $table) {
            $table->string('foto_1')->nullable()->after('upload_do');
            $table->string('foto_2')->nullable()->after('foto_1');
            $table->string('foto_3')->nullable()->after('foto_2');
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
            $table->dropColumn(['foto_1', 'foto_2', 'foto_3']);
        });
    }
}
