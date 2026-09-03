<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApFloorPerangkatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ap_floor_perangkat', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ap_floor_id');
            $table->unsignedBigInteger('id_perangkat');
            $table->timestamps();

            $table->index('ap_floor_id');
            $table->index('id_perangkat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ap_floor_perangkat');
    }
}
