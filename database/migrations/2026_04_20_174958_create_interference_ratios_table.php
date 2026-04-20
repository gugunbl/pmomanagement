<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInterferenceRatiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interference_ratios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_project');
            $table->date('tanggal');
            $table->string('ap_name');
            $table->integer('channel_before');
            $table->integer('channel_after');
            $table->integer('utilisasi_before'); // Stored as integer, e.g. 61
            $table->integer('utilisasi_after');  // Stored as integer, e.g. 9
            $table->string('waktu'); // e.g. 07.00 WIB
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            // Note: Not adding foreign keys strictly here assuming some tables might not use InnoDB or might be dropped frequently in this DB, but relationships will be on models.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('interference_ratios');
    }
}
