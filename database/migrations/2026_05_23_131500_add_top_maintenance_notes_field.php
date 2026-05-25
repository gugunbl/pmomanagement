<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTopMaintenanceNotesField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('m_project', function (Blueprint $table) {
            if (!Schema::hasColumn('m_project', 'top_maintenance_notes')) {
                $table->text('top_maintenance_notes')->nullable()->after('top_maintenance_signer_total');
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
            if (Schema::hasColumn('m_project', 'top_maintenance_notes')) {
                $table->dropColumn('top_maintenance_notes');
            }
        });
    }
}
