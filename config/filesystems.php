<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    'project_report_disk' => env('PROJECT_REPORT_STORAGE_DISK', 'public'),
    'project_report_path' => env('PROJECT_REPORT_STORAGE_PATH', 'project_reports/files'),
    'project_report_max_upload_kb' => env('PROJECT_REPORT_MAX_UPLOAD_KB', 51200),
    'project_report_nextcloud_webdav_url' => env('PROJECT_REPORT_NEXTCLOUD_WEBDAV_URL', ''),
    'project_report_nextcloud_browser_url' => env('PROJECT_REPORT_NEXTCLOUD_BROWSER_URL', ''),
    'project_report_nextcloud_username' => env('PROJECT_REPORT_NEXTCLOUD_USERNAME', ''),
    'project_report_nextcloud_password' => env('PROJECT_REPORT_NEXTCLOUD_APP_PASSWORD', ''),
    'project_report_nextcloud_root' => env('PROJECT_REPORT_NEXTCLOUD_ROOT', 'IT PMO/Project'),
    'project_report_nextcloud_standard_subdirectories' => [
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
    'project_report_nextcloud_subdirectory' => env('PROJECT_REPORT_NEXTCLOUD_SUBDIRECTORY', '30_Preventive_Maintenance'),
    'project_report_nextcloud_document_subdirectory' => env('PROJECT_REPORT_NEXTCLOUD_DOCUMENT_SUBDIRECTORY', '31_Document_Maintenance'),
    'project_report_nextcloud_timeout' => env('PROJECT_REPORT_NEXTCLOUD_TIMEOUT', 120),
      'project_ba_disk' => env('PROJECT_BA_STORAGE_DISK', 'nextcloud'),
      'project_ba_path' => env('PROJECT_BA_STORAGE_PATH', 'project_ba/files'),
    'case_document_disk' => env('CASE_DOCUMENT_STORAGE_DISK', env('PROJECT_REPORT_STORAGE_DISK', 'public')),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'project_reports_synology_ftp' => [
            'driver' => 'ftp',
            'host' => env('PROJECT_REPORT_FTP_HOST'),
            'username' => env('PROJECT_REPORT_FTP_USERNAME'),
            'password' => env('PROJECT_REPORT_FTP_PASSWORD'),
            'port' => env('PROJECT_REPORT_FTP_PORT', 21),
            'root' => env('PROJECT_REPORT_FTP_ROOT', '/'),
            'passive' => env('PROJECT_REPORT_FTP_PASSIVE', true),
            'ssl' => env('PROJECT_REPORT_FTP_SSL', false),
            'timeout' => env('PROJECT_REPORT_FTP_TIMEOUT', 30),
        ],

        'project_reports_synology_sftp' => [
            'driver' => 'sftp',
            'host' => env('PROJECT_REPORT_SFTP_HOST'),
            'username' => env('PROJECT_REPORT_SFTP_USERNAME'),
            'password' => env('PROJECT_REPORT_SFTP_PASSWORD'),
            'port' => env('PROJECT_REPORT_SFTP_PORT', 22),
            'root' => env('PROJECT_REPORT_SFTP_ROOT', '/'),
            'timeout' => env('PROJECT_REPORT_SFTP_TIMEOUT', 30),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
