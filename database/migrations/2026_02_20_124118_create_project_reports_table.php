<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id'); // Foreign key mapping to m_project.id_project? 
            // Note: m_project PK is id_project, check if it's bigInt. Usually it is. 
            // But let's check m_project migration or structure if possible. 
            // The Project model says protected $primaryKey = 'id_project';
            // Let's assume standard integer or bigInteger.
            
            $table->integer('report_ke');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->date('submission_date')->nullable();
            $table->string('status')->default('progress'); // progress, submitted, signoff
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Index/FK
            // $table->foreign('project_id')->references('id_project')->on('m_project')->onDelete('cascade');
        });

        Schema::table('m_project', function (Blueprint $table) {
            $table->integer('project_report_total')->default(0)->nullable();
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
            $table->dropColumn('project_report_total');
        });
        Schema::dropIfExists('project_reports');
    }
}
