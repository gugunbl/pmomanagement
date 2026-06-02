<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddDailyMonitoringPermission extends Migration
{
    public function up()
    {
        DB::table('permissions')->updateOrInsert(
            ['name' => 'sub-menu-daily-monitoring', 'guard_name' => 'web'],
            ['created_at' => now(), 'updated_at' => now()]
        );
    }

    public function down()
    {
        DB::table('permissions')
            ->where('name', 'sub-menu-daily-monitoring')
            ->where('guard_name', 'web')
            ->delete();
    }
}
