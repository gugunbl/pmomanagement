<?php

namespace App\Support;

final class NextcloudFilename
{
    private const PROJECT_NAME_MAX_CHARACTERS = 70;
    private const PROJECT_NAME_MAX_BYTES = 120;

    public static function projectName($value)
    {
        return self::cleanSegment($value, self::PROJECT_NAME_MAX_CHARACTERS, self::PROJECT_NAME_MAX_BYTES, 'Project', 50);
    }

    public static function teamName($value)
    {
        return self::cleanSegment($value, 45, 80, 'Team', 25);
    }

    private static function cleanSegment($value, $maxCharacters, $maxBytes, $fallback, $minimumWordBoundary)
    {
        $segment = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '-', (string) $value);
        $segment = preg_replace('/[\x00-\x1F\x7F]/u', '', $segment);
        $segment = preg_replace('/\s+/u', ' ', trim($segment));
        $segment = trim($segment, " .");

        if ($segment === '') {
            return $fallback;
        }

        $fullSegment = $segment;
        $segment = mb_substr($segment, 0, $maxCharacters, 'UTF-8');

        if (mb_strlen($fullSegment, 'UTF-8') > $maxCharacters) {
            $lastSpace = mb_strrpos($segment, ' ', 0, 'UTF-8');
            if ($lastSpace !== false && $lastSpace >= $minimumWordBoundary) {
                $segment = mb_substr($segment, 0, $lastSpace, 'UTF-8');
            }
        }

        $segment = mb_strcut($segment, 0, $maxBytes, 'UTF-8');

        return rtrim($segment, " .");
    }
}
