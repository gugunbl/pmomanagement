<?php

namespace App\Support;

final class NextcloudFilename
{
    private const PROJECT_NAME_MAX_CHARACTERS = 70;
    private const PROJECT_NAME_MAX_BYTES = 120;

    public static function projectName($value)
    {
        $projectName = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', (string) $value);
        $projectName = preg_replace('/[\x00-\x1F\x7F]/u', '', $projectName);
        $projectName = preg_replace('/\s+/u', ' ', trim($projectName));
        $projectName = trim($projectName, " .");

        if ($projectName === '') {
            return 'Project';
        }

        $fullProjectName = $projectName;
        $projectName = mb_substr($projectName, 0, self::PROJECT_NAME_MAX_CHARACTERS, 'UTF-8');

        if (mb_strlen($fullProjectName, 'UTF-8') > self::PROJECT_NAME_MAX_CHARACTERS) {
            $lastSpace = mb_strrpos($projectName, ' ', 0, 'UTF-8');
            if ($lastSpace !== false && $lastSpace >= 50) {
                $projectName = mb_substr($projectName, 0, $lastSpace, 'UTF-8');
            }
        }

        $projectName = mb_strcut($projectName, 0, self::PROJECT_NAME_MAX_BYTES, 'UTF-8');

        return rtrim($projectName, " .");
    }
}
