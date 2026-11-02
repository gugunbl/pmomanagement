<?php

namespace Tests\Unit;

use App\Project;
use App\ProjectReport;
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

    public function test_report_path_rejects_folder_names_with_spaces()
    {
        $this->configureNextcloud();
        $service = new NextcloudWebDav($this->client([new Response(201)]));

        $this->expectException(NextcloudWebDavException::class);
        $service->reportPath($this->makeReport('2025 VDI Phase2'));
    }

    private function configureNextcloud()
    {
        config([
            'filesystems.project_report_nextcloud_webdav_url' => 'https://cloud.example/remote.php/dav/files',
            'filesystems.project_report_nextcloud_username' => 'pmomaintenance',
            'filesystems.project_report_nextcloud_password' => 'app-password',
            'filesystems.project_report_nextcloud_root' => 'IT PMO/Project',
            'filesystems.project_report_nextcloud_subdirectory' => '30_Preventive_Maintenance',
            'filesystems.project_report_nextcloud_timeout' => 10,
        ]);
    }

    private function makeReport($folder)
    {
        $project = new Project();
        $project->link_dokumen = $folder;

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
