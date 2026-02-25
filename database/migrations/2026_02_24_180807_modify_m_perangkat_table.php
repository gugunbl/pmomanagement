<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyMPerangkatTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_perangkat', function (Blueprint $table) {
            $table->string('dimensi')->nullable()->after('jenis');
            $table->string('koordinat')->nullable()->after('dimensi');
            $table->unsignedBigInteger('id_lokasi')->nullable()->after('site');
            $table->string('status_server')->nullable()->after('status');
            $table->unsignedBigInteger('id_warehouse_transaksi')->nullable()->after('id_project');
            $table->string('upload_do')->nullable()->after('keterangan');
            $table->string('type_lisensi')->nullable()->after('upload_do');
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
            $table->dropColumn([
                'dimensi', 'koordinat', 'id_lokasi', 'status_server', 
                'id_warehouse_transaksi', 'upload_do', 'type_lisensi'
            ]);
        });
    }
}
