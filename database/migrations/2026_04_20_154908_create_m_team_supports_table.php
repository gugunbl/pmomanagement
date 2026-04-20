<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMTeamSupportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_team_supports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('project_team_support', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('team_support_id');
            $table->timestamps();

            // Project table uses id_project as PK
            // Assuming id_project is an integer (usually unsigned in Laravel)
            // But let's check its type in project table if possible, usually it's unsignedBigInteger in modern Laravel
            // or integer if it's older.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_team_support');
        Schema::dropIfExists('m_team_supports');
    }
}
