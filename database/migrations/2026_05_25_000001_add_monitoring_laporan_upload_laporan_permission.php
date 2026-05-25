<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddMonitoringLaporanUploadLaporanPermission extends Migration
{
    public function up()
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'monitoring-laporan-upload-laporan', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down()
    {
        DB::table('permissions')
            ->where('name', 'monitoring-laporan-upload-laporan')
            ->where('guard_name', 'web')
            ->delete();
    }
}
