<?php

namespace Tests\Feature;

use App\Http\Controllers\ProjectController;
use App\ProjectReport;
use App\ProjectReportTeamFile;
use App\Services\NextcloudWebDav;
use App\Services\ProjectReportTeamFiles;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ProjectReportNextcloudRegistrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('m_project', function (Blueprint $table) {
            $table->unsignedBigInteger('id_project')->primary();
            $table->string('nama_project');
            $table->string('link_dokumen');
        });

        Schema::create('project_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedInteger('report_ke');
            $table->string('status');
            $table->text('uploaded_file')->nullable();
            $table->string('uploaded_disk')->nullable();
            $table->text('uploaded_original_name')->nullable();
            $table->timestamps();
        });

        Schema::create('m_team_supports', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('project_team_support', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('team_support_id');
            $table->timestamps();
        });

        Schema::create('project_report_team_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->unsignedBigInteger('team_support_id');
            $table->text('uploaded_file')->nullable();
            $table->string('uploaded_disk')->nullable();
            $table->text('uploaded_original_name')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_report_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_report_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('comment');
            $table->timestamps();
        });

        DB::table('m_project')->insert([
            'id_project' => 10,
            'nama_project' => 'Project Besar',
            'link_dokumen' => '2026_Project_Besar',
        ]);
        DB::table('project_reports')->insert([
            'id' => 129,
            'project_id' => 10,
            'report_ke' => 6,
            'status' => 'signoff',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_existing_nextcloud_pdf_can_be_verified_and_linked_without_uploading_again(): void
    {
        $user = new class {
            public function can($permission)
            {
                return $permission === 'monitoring-laporan-upload-laporan';
            }
        };
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn(7);

        $fileName = 'Laporan_PM_Besar.pdf';
        $path = 'IT PMO/Project/2026_Project_Besar/30_Preventive_Maintenance/' . $fileName;
        $nextcloud = Mockery::mock(NextcloudWebDav::class);
        $nextcloud->shouldReceive('reportPath')->once()->andReturn($path);
        $nextcloud->shouldReceive('fileMetadata')->once()->with($path)->andReturn([
            'exists' => true,
            'is_collection' => false,
            'size' => 73400320,
            'content_type' => 'application/pdf',
        ]);
        $this->app->instance(NextcloudWebDav::class, $nextcloud);

        $report = ProjectReport::findOrFail(129);
        $request = Request::create('/project/report-monitoring/129/register-nextcloud', 'POST', [
            'existing_file_name' => $fileName,
        ]);

        app(ProjectController::class)->reportRegisterNextcloud($request, $report);

        $this->assertDatabaseHas('project_reports', [
            'id' => 129,
            'uploaded_disk' => 'nextcloud',
            'uploaded_file' => $path,
            'uploaded_original_name' => $fileName,
        ]);
        $this->assertDatabaseHas('project_report_comments', [
            'project_report_id' => 129,
            'user_id' => 7,
            'comment' => 'File Nextcloud dihubungkan ke laporan: ' . $fileName,
        ]);
    }

    public function test_project_team_mapping_creates_slots_and_links_pdf_to_the_selected_team(): void
    {
        DB::table('m_team_supports')->insert([
            'id' => 4,
            'name' => 'SAP GTI - Compute',
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('project_team_support')->insert([
            'project_id' => 10,
            'team_support_id' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = new class {
            public function can($permission)
            {
                return $permission === 'monitoring-laporan-upload-laporan';
            }
        };
        Auth::shouldReceive('user')->andReturn($user);
        Auth::shouldReceive('id')->andReturn(7);

        $report = ProjectReport::findOrFail(129);
        $slots = app(ProjectReportTeamFiles::class)->ensureSlots($report);
        $this->assertCount(1, $slots);
        $this->assertSame(4, (int) $slots->first()->team_support_id);

        $teamFile = ProjectReportTeamFile::findOrFail($slots->first()->id);
        $fileName = 'Laporan_SAP_GTI_Compute.pdf';
        $path = 'IT PMO/Project/2026_Project_Besar/30_Preventive_Maintenance/' . $fileName;
        $nextcloud = Mockery::mock(NextcloudWebDav::class);
        $nextcloud->shouldReceive('reportTeamPath')->once()->andReturn($path);
        $nextcloud->shouldReceive('fileMetadata')->once()->with($path)->andReturn([
            'exists' => true,
            'is_collection' => false,
            'size' => 104857600,
            'content_type' => 'application/pdf',
        ]);
        $this->app->instance(NextcloudWebDav::class, $nextcloud);

        $request = Request::create('/project/report-monitoring/129/team-files/' . $teamFile->id . '/register-nextcloud', 'POST', [
            'existing_file_name' => $fileName,
            'team_file_id' => $teamFile->id,
        ]);
        app(ProjectController::class)->reportTeamFileRegisterNextcloud($request, $report, $teamFile);

        $this->assertDatabaseHas('project_report_team_files', [
            'id' => $teamFile->id,
            'uploaded_disk' => 'nextcloud',
            'uploaded_file' => $path,
            'file_size' => 104857600,
            'uploaded_by' => 7,
        ]);
    }
}
