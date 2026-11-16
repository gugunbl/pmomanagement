<?php

$versionFile = base_path('VERSION');
$fileVersion = is_file($versionFile) ? trim((string) file_get_contents($versionFile)) : '0.0.0';
$environmentVersion = env('APP_VERSION');

return [
    /*
    | The VERSION file is the source of truth for normal releases.
    | APP_VERSION can override it for an environment-specific deployment.
    */
    'release' => is_string($environmentVersion) && trim($environmentVersion) !== ''
        ? trim($environmentVersion)
        : $fileVersion,
];
