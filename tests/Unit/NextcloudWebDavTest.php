<?php

namespace Tests\Unit;

use App\Project;
use App\ProjectReport;
use App\ProjectTopMaintenance;
use App\ProjectReportTeamFile;
use App\Services\NextcloudWebDav;
use App\Services\NextcloudWebDavException;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Tests\TestCase;

class NextcloudWebDavTest extends TestCase
{
    public function test_report_path_uses_configured_root_project_and_subdirectory()
    {
        $this->configureNextcloud();
        $report = $this->makeReport('2025_VDI_Phase2');

        $service = new NextcloudWebDav($this->client([new Response(201)]));

        $this->assertSame(
            'IT PMO/Project/2025_VDI_Phase2/30_Preventive_Maintenance/report-42.pdf',
            $service->reportPath($report)
        );
    }

    public function test_upload_filename_uses_upload_date_project_report_number_and_period()
    {
        $this->configureNextcloud();
        $report = $this->makeReport(
            '2021_Pengadaan_Server_MDM',
            'Pengadaan Penambahan Server MDM Database Tahun 2021'
        );
        $report->report_ke = 1;
        $report->period_start = '2000-01-01';
        $report->period_end = '2000-01-01';

        $fileName = '20261112_Laporan_PM_Pengadaan Penambahan Server MDM Database Tahun 2021_1_20000101_20000101.pdf';

        $this->assertSame($fileName, $report->uploadFilename('2026-11-12'));
        $this->assertSame(
            'IT PMO/Project/2021_Pengadaan_Server_MDM/30_Preventive_Maintenance/' . $fileName,
            (new NextcloudWebDav($this->client([new Response(201)])))->reportPath($report, $fileName)
        );
    }

    public function test_team_report_filename_keeps_existing_format_and_adds_sanitized_team_name()
    {
        $this->configureNextcloud();
        $report = $this->makeReport('2024_SAP', 'SAP BRIFIRST Project');
        $report->report_ke = 6;
        $report->period_start = '2026-09-01';
        $report->period_end = '2026-12-31';
        $team = new \App\TeamSupport(['name' => 'SAP GTI - Compute']);

        $teamFile = new ProjectReportTeamFile();
        $teamFile->setRelation('report', $report);
        $teamFile->setRelation('teamSupport', $team);
        $fileName = '20261119_Laporan_PM_SAP BRIFIRST Project_6_SAP GTI - Compute_20260901_20261231.pdf';

        $this->assertSame($fileName, $teamFile->uploadFilename('2026-11-19'));
        $this->assertSame(
            'IT PMO/Project/2024_SAP/30_Preventive_Maintenance/' . $fileName,
            (new NextcloudWebDav($this->client([new Response(207)])))->reportTeamPath($teamFile, $fileName)
        );
    }

    public function test_upload_filename_is_unavailable_without_both_period_dates()
    {
        $report = $this->makeReport('2021_Pengadaan_Server_MDM');

        $this->assertNull($report->uploadFilename('2026-11-12'));
    }

    public function test_storage_filename_keeps_legacy_format_and_limits_project_name()
    {
        $report = $this->makeReport(
            '2024_BBRI_SAP_BRIFIRST',
            str_repeat('Project Sangat Panjang ', 10)
        );
        $report->report_ke = 3;
        $report->period_start = '2026-01-01';
        $report->period_end = '2026-01-31';

        $fileName = $report->storageFilename('2026-11-17 17:27:16');
        $projectName = 'Project Sangat Panjang Project Sangat Panjang Project Sangat Panjang';
        $expected = '20261117_Laporan_PM_' . $projectName . '_3_20260101_20260131.pdf';

        $this->assertSame($expected, $fileName);
        $this->assertLessThanOrEqual(70, mb_strlen($projectName, 'UTF-8'));
        $this->assertLessThan(255, strlen($fileName));
    }

    public function test_upload_uses_basic_auth_and_encodes_path_segments()
    {
        $this->configureNextcloud();
        $history = [];
        $service = new NextcloudWebDav($this->client([new Response(201)], $history));
        $temporaryFile = tempnam(sys_get_temp_dir(), 'nextcloud-test');
        file_put_contents($temporaryFile, 'pdf content');

        try {
            $service->upload($temporaryFile, 'IT PMO/Project/2025_VDI_Phase2/report-42.pdf');
        } finally {
            unlink($temporaryFile);
        }

        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2025_VDI_Phase2/report-42.pdf',
            (string) $history[0]['request']->getUri()
        );
        $this->assertSame('PUT', $history[0]['request']->getMethod());
        $this->assertSame('Basic ' . base64_encode('pmomaintenance:app-password'), $history[0]['request']->getHeaderLine('Authorization'));
    }

    public function test_ensure_project_folders_creates_project_and_report_directories()
    {
        $this->configureNextcloud();
        $history = [];
        $directoryCount = 1 + count(config('filesystems.project_report_nextcloud_standard_subdirectories')) + 2;
        $responses = [];
        for ($i = 0; $i < $directoryCount; $i++) {
            $responses[] = new Response(404);
            $responses[] = new Response(201);
        }
        $service = new NextcloudWebDav($this->client($responses, $history));

        $service->ensureProjectFolders('2026_Project Baru');

        $expectedMethods = [];
        for ($i = 0; $i < $directoryCount; $i++) {
            $expectedMethods[] = 'PROPFIND';
            $expectedMethods[] = 'MKCOL';
        }
        $this->assertSame($expectedMethods, array_map(function ($item) {
            return $item['request']->getMethod();
        }, $history));
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2026_Project%20Baru',
            (string) $history[1]['request']->getUri()
        );
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2026_Project%20Baru/01.%20DOKUMEN%20AWAL',
            (string) $history[3]['request']->getUri()
        );
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2026_Project%20Baru/13.%20BERITA%20ACARA',
            (string) $history[27]['request']->getUri()
        );
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2026_Project%20Baru/30_Preventive_Maintenance',
            (string) $history[29]['request']->getUri()
        );
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2026_Project%20Baru/31_Document_Maintenance',
            (string) $history[31]['request']->getUri()
        );
    }

    public function test_ensure_project_folders_does_not_recreate_existing_directories()
    {
        $this->configureNextcloud();
        $history = [];
        $directoryCount = 1 + count(config('filesystems.project_report_nextcloud_standard_subdirectories')) + 2;
        $responses = array_map(function () {
            return new Response(207);
        }, range(1, $directoryCount));
        $service = new NextcloudWebDav($this->client($responses, $history));

        $service->ensureProjectFolders('2026_Project_Existing');

        $this->assertSame(array_fill(0, $directoryCount, 'PROPFIND'), array_map(function ($item) {
            return $item['request']->getMethod();
        }, $history));
    }

    public function test_case_document_path_uses_document_maintenance_directory()
    {
        $this->configureNextcloud();
        $project = new Project();
        $project->link_dokumen = '2026_Project_Baru';
        $service = new NextcloudWebDav($this->client([new Response(207)]));

        $this->assertSame(
            'IT PMO/Project/2026_Project_Baru/31_Document_Maintenance/20261118_RCA_case_10.pdf',
            $service->caseDocumentPath($project, '20261118_RCA_case_10.pdf')
        );
    }

    public function test_file_metadata_verifies_existing_pdf_through_propfind()
    {
        $this->configureNextcloud();
        $history = [];
        $xml = '<?xml version="1.0"?><d:multistatus xmlns:d="DAV:"><d:response><d:propstat><d:prop><d:getcontentlength>73400320</d:getcontentlength><d:getcontenttype>application/pdf</d:getcontenttype><d:resourcetype/></d:prop></d:propstat></d:response></d:multistatus>';
        $service = new NextcloudWebDav($this->client([new Response(207, ['Content-Type' => 'application/xml'], $xml)], $history));

        $metadata = $service->fileMetadata('IT PMO/Project/2025_VDI/30_Preventive_Maintenance/Laporan Besar.pdf');

        $this->assertTrue($metadata['exists']);
        $this->assertFalse($metadata['is_collection']);
        $this->assertSame(73400320, $metadata['size']);
        $this->assertSame('application/pdf', $metadata['content_type']);
        $this->assertSame('PROPFIND', $history[0]['request']->getMethod());
        $this->assertSame('0', $history[0]['request']->getHeaderLine('Depth'));
    }

    public function test_file_metadata_reports_missing_nextcloud_file()
    {
        $this->configureNextcloud();
        $service = new NextcloudWebDav($this->client([new Response(404)]));

        $metadata = $service->fileMetadata('IT PMO/Project/2025_VDI/30_Preventive_Maintenance/Tidak Ada.pdf');

        $this->assertFalse($metadata['exists']);
        $this->assertNull($metadata['size']);
    }

    public function test_ba_filename_and_path_use_document_directory_and_limited_project_name()
    {
        $this->configureNextcloud();
        $project = new Project();
        $project->link_dokumen = '2026_Project_Baru';
        $project->nama_project = str_repeat('Project Sangat Panjang ', 10);

        $ba = new ProjectTopMaintenance();
        $ba->top_ke = 4;
        $ba->setRelation('project', $project);

        $projectName = 'Project Sangat Panjang Project Sangat Panjang Project Sangat Panjang';
        $fileName = 'BA_TOP4_' . $projectName . '_20261118.pdf';
        $service = new NextcloudWebDav($this->client([new Response(207)]));

        $this->assertSame($fileName, $ba->uploadFilename('2026-11-18'));
        $this->assertSame(
            'IT PMO/Project/2026_Project_Baru/31_Document_Maintenance/' . $fileName,
            $service->maintenanceDocumentPath($project, $fileName)
        );
        $this->assertLessThan(255, strlen($fileName));
    }

    public function test_upload_does_not_close_a_stream_that_the_http_handler_already_closed()
    {
        $this->configureNextcloud();
        $handlerClosedStream = false;
        $handler = function ($request, array $options) use (&$handlerClosedStream) {
            $request->getBody()->close();
            $handlerClosedStream = true;

            return \GuzzleHttp\Promise\Create::promiseFor(new Response(201));
        };
        $service = new NextcloudWebDav(new Client([
            'handler' => HandlerStack::create($handler),
        ]));
        $temporaryFile = tempnam(sys_get_temp_dir(), 'nextcloud-test');
        file_put_contents($temporaryFile, 'pdf content');

        try {
            $service->upload($temporaryFile, 'IT PMO/Project/2025_VDI_Phase2/report-42.pdf');
        } finally {
            unlink($temporaryFile);
        }

        $this->assertTrue($handlerClosedStream);
    }

    public function test_delete_uses_webdav_delete_request()
    {
        $this->configureNextcloud();
        $history = [];
        $service = new NextcloudWebDav($this->client([new Response(204)], $history));

        $service->delete('IT PMO/Project/2025_VDI_Phase2/report-42.pdf');

        $this->assertSame('DELETE', $history[0]['request']->getMethod());
        $this->assertSame(
            'https://cloud.example/remote.php/dav/files/pmomaintenance/IT%20PMO/Project/2025_VDI_Phase2/report-42.pdf',
            (string) $history[0]['request']->getUri()
        );
    }

    public function test_report_path_rejects_folder_names_with_path_separators()
    {
        $this->configureNextcloud();
        $service = new NextcloudWebDav($this->client([new Response(201)]));

        $this->expectException(NextcloudWebDavException::class);
        $service->reportPath($this->makeReport('2025/VDI_Phase2'));
    }

    private function configureNextcloud()
    {
        config([
            'filesystems.project_report_nextcloud_webdav_url' => 'https://cloud.example/remote.php/dav/files',
            'filesystems.project_report_nextcloud_username' => 'pmomaintenance',
            'filesystems.project_report_nextcloud_password' => 'app-password',
            'filesystems.project_report_nextcloud_root' => 'IT PMO/Project',
            'filesystems.project_report_nextcloud_standard_subdirectories' => [
                '01. DOKUMEN AWAL',
                '02. KOM',
                '03. MOM',
                '04. TIMELINE',
                '05. PRE-REQUISITE',
                '06. DO',
                '07. PAT',
                '08. UAT',
                '09. DOKUMEN DESIGN',
                '10. DOKUMEN IMPLEMENTASI',
                '11. USER MANUAL - USER GUIDE',
                '12. TRANSFERKNOWLEDGE',
                '13. BERITA ACARA',
            ],
            'filesystems.project_report_nextcloud_subdirectory' => '30_Preventive_Maintenance',
            'filesystems.project_report_nextcloud_document_subdirectory' => '31_Document_Maintenance',
            'filesystems.project_report_nextcloud_timeout' => 10,
        ]);
    }

    private function makeReport($folder, $projectName = 'Project Name')
    {
        $project = new Project();
        $project->link_dokumen = $folder;
        $project->nama_project = $projectName;

        $report = new ProjectReport();
        $report->setAttribute('id', 42);
        $report->setRelation('project', $project);

        return $report;
    }

    private function client(array $responses, array &$history = null)
    {
        $stack = HandlerStack::create(new MockHandler($responses));
        if ($history !== null) {
            $stack->push(Middleware::history($history));
        }

        return new Client(['handler' => $stack]);
    }
}
