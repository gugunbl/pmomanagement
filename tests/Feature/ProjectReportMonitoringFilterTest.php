<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProjectReportMonitoringFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
        });

        Schema::create('m_project', function (Blueprint $table) {
            $table->unsignedBigInteger('id_project')->primary();
            $table->string('customer')->nullable();
            $table->string('nama_project');
            $table->string('nomor_spk')->nullable();
        });

        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->integer('report_ke');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->string('status')->default('progress');
        });

        Schema::create('project_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        Schema::create('project_report_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();
        });

        DB::table('m_project')->insert([
            ['id_project' => 1, 'customer' => 'Customer A', 'nama_project' => 'Alpha', 'nomor_spk' => 'SPK-1'],
            ['id_project' => 2, 'customer' => 'Customer A', 'nama_project' => 'Beta', 'nomor_spk' => 'SPK-2'],
            ['id_project' => 3, 'customer' => 'Customer B', 'nama_project' => 'Gamma', 'nomor_spk' => 'SPK-3'],
        ]);

        DB::table('project_reports')->insert([
            ['id' => 11, 'project_id' => 1, 'report_ke' => 1, 'period_end' => '2026-01-31', 'status' => 'progress'],
            ['id' => 12, 'project_id' => 2, 'report_ke' => 1, 'period_end' => '2026-02-28', 'status' => 'signoff'],
            ['id' => 13, 'project_id' => 3, 'report_ke' => 1, 'period_end' => '2026-03-31', 'status' => 'progress'],
        ]);
    }

    public function test_customer_and_project_filters_return_only_matching_reports_and_projects(): void
    {
        $request = Request::create('/project/report-monitoring', 'GET', [
            'customer' => 'Customer A',
            'project' => '2',
        ]);

        $viewData = app(ProjectController::class)->reportMonitoring($request)->getData();

        $this->assertSame([12], $viewData['reports']->getCollection()->pluck('id')->all());
        $this->assertSame(['Customer A', 'Customer B'], $viewData['customerOptions']->all());
        $this->assertSame([1, 2], $viewData['projectOptions']->pluck('id_project')->all());
        $this->assertSame(2, $viewData['selectedProjectId']);
    }

    public function test_project_options_are_loaded_only_after_a_customer_is_selected(): void
    {
        $viewData = app(ProjectController::class)
            ->reportMonitoring(Request::create('/project/report-monitoring', 'GET'))
            ->getData();

        $this->assertTrue($viewData['projectOptions']->isEmpty());
        $this->assertSame(3, $viewData['reports']->total());
    }
}
