<?php

namespace App\Services;

use App\Project;
use App\ProjectReport;
use App\ProjectReportTeamFile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class NextcloudWebDav
{
    private $client;
    private $webdavUrl;
    private $username;
    private $password;
    private $timeout;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
        $this->webdavUrl = rtrim(trim(config('filesystems.project_report_nextcloud_webdav_url', '')), '/');
        $this->username = trim(config('filesystems.project_report_nextcloud_username', ''));
        $this->password = (string) config('filesystems.project_report_nextcloud_password', '');
        $this->timeout = (int) config('filesystems.project_report_nextcloud_timeout', 60);

        if ($this->webdavUrl === '' || $this->username === '' || $this->password === '') {
            throw new NextcloudWebDavException('Konfigurasi WebDAV Nextcloud belum lengkap.');
        }

        if (parse_url($this->webdavUrl, PHP_URL_SCHEME) !== 'https') {
            throw new NextcloudWebDavException('URL WebDAV Nextcloud harus menggunakan HTTPS.');
        }
    }

    public function reportPath(ProjectReport $report, $fileName = null)
    {
        $project = $report->project;
        $projectFolder = $project ? (string) $project->link_dokumen : '';
        $subdirectory = trim((string) config('filesystems.project_report_nextcloud_subdirectory', '30_Preventive_Maintenance'));

        if (!Project::isValidNextcloudFolderName($projectFolder)) {
            throw new NextcloudWebDavException('Nama folder project belum diisi atau mengandung karakter path yang tidak diperbolehkan.');
        }

        if (!Project::isValidNextcloudFolderName($subdirectory)) {
            throw new NextcloudWebDavException('Subfolder laporan Nextcloud belum dikonfigurasi atau mengandung karakter path yang tidak diperbolehkan.');
        }

        $root = trim((string) config('filesystems.project_report_nextcloud_root', ''), '/');
        $segments = $root === '' ? [] : explode('/', $root);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new NextcloudWebDavException('Folder induk Nextcloud tidak valid.');
            }
        }

        $fileName = $fileName ?: 'report-' . $report->getKey() . '.pdf';
        if (strpos($fileName, '/') !== false || strpos($fileName, '\\') !== false || $fileName === '.' || $fileName === '..') {
            throw new NextcloudWebDavException('Nama file laporan tidak valid.');
        }

        $segments[] = $projectFolder;
        $segments[] = $subdirectory;
        $segments[] = $fileName;

        return implode('/', $segments);
    }

    public function reportTeamPath(ProjectReportTeamFile $teamFile, $fileName = null)
    {
        $teamFile->loadMissing('report.project');
        $fileName = $fileName ?: $teamFile->uploadFilename();

        return $this->reportPath($teamFile->report, $fileName);
    }

    /**
     * Ensure the project folder and all configured maintenance subfolders exist.
     * Existing folders are intentionally treated as a successful, idempotent result.
     */
    public function ensureProjectFolders($projectFolder)
    {
        $projectFolder = trim((string) $projectFolder);
        $standardSubdirectories = config('filesystems.project_report_nextcloud_standard_subdirectories', []);
        if (!is_array($standardSubdirectories)) {
            throw new NextcloudWebDavException('Konfigurasi struktur folder standard Nextcloud harus berupa daftar folder.');
        }

        $subdirectories = array_map('trim', array_merge($standardSubdirectories, [
            trim((string) config('filesystems.project_report_nextcloud_subdirectory', '30_Preventive_Maintenance')),
            trim((string) config('filesystems.project_report_nextcloud_document_subdirectory', '31_Document_Maintenance')),
        ]));
        $subdirectories = array_values(array_unique(array_filter($subdirectories, function ($directory) {
            return $directory !== '';
        })));

        if (!Project::isValidNextcloudFolderName($projectFolder)) {
            throw new NextcloudWebDavException('Nama folder project belum diisi atau mengandung karakter path yang tidak diperbolehkan.');
        }

        foreach ($subdirectories as $subdirectory) {
            if (!Project::isValidNextcloudFolderName($subdirectory)) {
                throw new NextcloudWebDavException('Subfolder Nextcloud belum dikonfigurasi atau mengandung karakter path yang tidak diperbolehkan.');
            }
        }

        $root = trim((string) config('filesystems.project_report_nextcloud_root', ''), '/');
        $segments = $root === '' ? [] : explode('/', $root);

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new NextcloudWebDavException('Folder induk Nextcloud tidak valid.');
            }
        }

        $segments[] = $projectFolder;
        $projectPath = implode('/', $segments);
        $this->ensureDirectory($projectPath);

        foreach ($subdirectories as $subdirectory) {
            $this->ensureDirectory($projectPath . '/' . $subdirectory);
        }
    }

    public function caseDocumentPath(Project $project, $fileName)
    {
        return $this->maintenanceDocumentPath($project, $fileName);
    }

    public function maintenanceDocumentPath(Project $project, $fileName)
    {
        $projectFolder = trim((string) $project->link_dokumen);
        $subdirectory = trim((string) config('filesystems.project_report_nextcloud_document_subdirectory', '31_Document_Maintenance'));

        if (!Project::isValidNextcloudFolderName($projectFolder)) {
            throw new NextcloudWebDavException('Folder Project di Nextcloud belum diisi atau tidak valid.');
        }

        if (!Project::isValidNextcloudFolderName($subdirectory)) {
            throw new NextcloudWebDavException('Subfolder dokumen maintenance belum dikonfigurasi dengan benar.');
        }

        if ($fileName === '' || strpos($fileName, '/') !== false || strpos($fileName, '\\') !== false) {
            throw new NextcloudWebDavException('Nama dokumen maintenance tidak valid.');
        }

        $root = trim((string) config('filesystems.project_report_nextcloud_root', ''), '/');

        return implode('/', array_filter([$root, $projectFolder, $subdirectory, $fileName], function ($segment) {
            return $segment !== '';
        }));
    }

    public function upload($localPath, $remotePath)
    {
        if (!is_file($localPath) || !is_readable($localPath)) {
            throw new NextcloudWebDavException('File upload sementara tidak dapat dibaca.');
        }

        $stream = fopen($localPath, 'rb');
        if ($stream === false) {
            throw new NextcloudWebDavException('File upload sementara tidak dapat dibuka.');
        }

        try {
            $this->request('PUT', $remotePath, ['body' => $stream]);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    public function download($remotePath)
    {
        return $this->request('GET', $remotePath, ['stream' => true]);
    }

    public function fileMetadata($remotePath)
    {
        $response = $this->request('PROPFIND', $remotePath, [
            'headers' => ['Depth' => '0'],
        ], [200, 207, 404]);

        if ($response->getStatusCode() === 404) {
            return [
                'exists' => false,
                'is_collection' => false,
                'size' => null,
                'content_type' => null,
            ];
        }

        $metadata = [
            'exists' => true,
            'is_collection' => false,
            'size' => null,
            'content_type' => null,
        ];
        $xml = (string) $response->getBody();

        if ($xml === '' || !class_exists(\DOMDocument::class)) {
            return $metadata;
        }

        $previousUseInternalErrors = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadXML($xml, LIBXML_NONET | LIBXML_NOBLANKS);
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseInternalErrors);

        if (!$loaded) {
            return $metadata;
        }

        $xpath = new \DOMXPath($document);
        $sizeNode = $xpath->query('//*[local-name()="getcontentlength"]')->item(0);
        $typeNode = $xpath->query('//*[local-name()="getcontenttype"]')->item(0);

        $metadata['is_collection'] = $xpath->query('//*[local-name()="resourcetype"]/*[local-name()="collection"]')->length > 0;
        $metadata['size'] = $sizeNode && is_numeric(trim($sizeNode->textContent))
            ? (int) trim($sizeNode->textContent)
            : null;
        $metadata['content_type'] = $typeNode ? trim($typeNode->textContent) : null;

        return $metadata;
    }

    public function delete($remotePath)
    {
        return $this->request('DELETE', $remotePath);
    }

    private function ensureDirectory($remotePath)
    {
        $response = $this->request('PROPFIND', $remotePath, [
            'headers' => ['Depth' => '0'],
        ], [200, 207, 404]);

        if ($response->getStatusCode() !== 404) {
            return;
        }

        // HTTP 405 can occur when another request created the same folder
        // between PROPFIND and MKCOL, so it is safe to treat it as existing.
        $this->request('MKCOL', $remotePath, [], [201, 405]);
    }

    private function request($method, $remotePath, array $options = [], array $acceptedStatusCodes = [])
    {
        $url = $this->urlForPath($remotePath);
        $options['auth'] = [$this->username, $this->password];
        $options['http_errors'] = false;
        $options['timeout'] = $this->timeout;
        $options['verify'] = true;

        try {
            $response = $this->client->request($method, $url, $options);
        } catch (GuzzleException $e) {
            throw new NextcloudWebDavException(
                'Tidak dapat terhubung ke Nextcloud. Periksa URL WebDAV, koneksi server, dan sertifikat HTTPS.',
                0,
                $e
            );
        }

        $statusCode = $response->getStatusCode();
        $isAccepted = in_array($statusCode, $acceptedStatusCodes, true)
            || (empty($acceptedStatusCodes) && $statusCode >= 200 && $statusCode < 300);

        if (!$isAccepted) {
            if ($statusCode === 401 || $statusCode === 403) {
                $message = 'Periksa username/app password Nextcloud dan izin tulis/baca pada folder tujuan.';
            } elseif ($statusCode === 404 || $statusCode === 409) {
                $message = 'Folder tujuan Nextcloud tidak ditemukan. Periksa folder induk, nama folder project, dan subfolder.';
            } else {
                $message = 'Periksa konfigurasi WebDAV, koneksi server, dan izin folder.';
            }

            throw new NextcloudWebDavException(
                'Nextcloud menolak permintaan WebDAV (HTTP ' . $statusCode . '). ' . $message,
                $statusCode
            );
        }

        return $response;
    }

    private function urlForPath($remotePath)
    {
        $segments = explode('/', trim((string) $remotePath, '/'));
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new NextcloudWebDavException('Path file Nextcloud tidak valid.');
            }
        }

        $encodedPath = implode('/', array_map('rawurlencode', $segments));

        return $this->webdavUrl . '/' . rawurlencode($this->username) . '/' . $encodedPath;
    }
}
