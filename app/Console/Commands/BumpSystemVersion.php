<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RuntimeException;

class BumpSystemVersion extends Command
{
    protected $signature = 'system:version:bump
                            {level=patch : Version level: patch, minor, or major}
                            {--dry-run : Show the next version without changing VERSION}';

    protected $description = 'Increment the system release version stored in the VERSION file';

    public function handle(): int
    {
        $level = strtolower((string) $this->argument('level'));

        if (! in_array($level, ['patch', 'minor', 'major'], true)) {
            $this->error('Level must be patch, minor, or major.');

            return self::INVALID;
        }

        $versionPath = base_path('VERSION');
        $current = is_file($versionPath) ? trim((string) file_get_contents($versionPath)) : '0.0.0';

        if (! preg_match('/^(\d+)\.(\d+)(?:\.(\d+))?$/', $current, $matches)) {
            throw new RuntimeException("Invalid version in VERSION: {$current}");
        }

        $parts = [
            (int) $matches[1],
            (int) $matches[2],
            isset($matches[3]) ? (int) $matches[3] : 0,
        ];

        if ($level === 'major') {
            $parts = [$parts[0] + 1, 0, 0];
        } elseif ($level === 'minor') {
            $parts = [$parts[0], $parts[1] + 1, 0];
        } else {
            $parts[2]++;
        }

        $next = implode('.', $parts);

        if ($this->option('dry-run')) {
            $this->info("{$current} -> {$next} (dry run)");

            return self::SUCCESS;
        }

        if (file_put_contents($versionPath, $next.PHP_EOL, LOCK_EX) === false) {
            throw new RuntimeException('Unable to update the VERSION file.');
        }

        $this->callSilent('config:clear');
        $this->info("System version updated: {$current} -> {$next}");
        $this->comment('Commit VERSION together with the release changes.');

        return self::SUCCESS;
    }
}
