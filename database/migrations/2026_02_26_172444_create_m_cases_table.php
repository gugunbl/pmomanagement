<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('m_cases', function (Blueprint $table) {
            $table->bigIncrements('id_case');
            $table->unsignedInteger('id_project')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('open_date');
            $table->string('subject');
            $table->text('detail');
            $table->string('severity')->comment('Minor, Major, Critical');
            $table->string('affected_sn')->nullable();
            $table->string('location')->nullable();
            $table->string('principal_ticket_no')->nullable();
            $table->text('resolution_detail')->nullable();
            $table->string('status')->default('Open')->comment('Open, In Progress, Closed');
            $table->timestamps();

            $table->index('id_project');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('m_cases');
    }
}
