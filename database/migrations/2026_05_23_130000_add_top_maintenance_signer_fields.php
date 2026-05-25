<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTopMaintenanceSignerFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_project', function (Blueprint $table) {
            if (!Schema::hasColumn('m_project', 'top_maintenance_signer_total')) {
                $table->integer('top_maintenance_signer_total')->nullable()->default(0)->after('top_maintenance_total');
            }
        });

        Schema::table('project_top_maintenance', function (Blueprint $table) {
            if (!Schema::hasColumn('project_top_maintenance', 'signers')) {
                $table->text('signers')->nullable()->after('keterangan');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('m_project', function (Blueprint $table) {
            if (Schema::hasColumn('m_project', 'top_maintenance_signer_total')) {
                $table->dropColumn('top_maintenance_signer_total');
            }
        });

        Schema::table('project_top_maintenance', function (Blueprint $table) {
            if (Schema::hasColumn('project_top_maintenance', 'signers')) {
                $table->dropColumn('signers');
            }
        });
    }
}
