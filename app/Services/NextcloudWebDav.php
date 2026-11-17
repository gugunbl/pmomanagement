<?php

namespace App\Services;

use App\Project;
use App\ProjectReport;
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
        $subdirectory = trim((string) config('filesystems.project_report_nextcloud_subdirectory', ''));

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

    public function delete($remotePath)
    {
        return $this->request('DELETE', $remotePath);
    }

    private function request($method, $remotePath, array $options = [])
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
        if ($statusCode < 200 || $statusCode >= 300) {
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
