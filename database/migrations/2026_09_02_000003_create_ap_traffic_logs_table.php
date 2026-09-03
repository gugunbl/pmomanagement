<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApTrafficLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ap_traffic_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_lokasi');
            $table->unsignedBigInteger('ap_floor_id')->nullable();
            $table->string('nama_lantai');
            $table->date('tanggal');
            $table->decimal('traffic_value', 12, 2)->default(0);
            $table->string('traffic_unit', 10)->default('GB'); // KB, MB, GB, TB
            $table->decimal('traffic_in_mb', 16, 4)->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['id_lokasi', 'nama_lantai', 'tanggal'], 'traffic_lokasi_lantai_tanggal_unique');
            $table->index(['id_lokasi', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ap_traffic_logs');
    }
}
