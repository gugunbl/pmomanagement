<?php

namespace App\Console\Commands;

use App\CaseDocument;
use App\MCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class AuditCaseDocuments extends Command
{
    protected $signature = 'cases:documents:audit';
    protected $description = 'Audit dokumen case lama dan dokumen baru tanpa mengubah atau memindahkan file';

    public function handle()
    {
        $legacyFields = [
            'evidence_photo' => 'Evidence',
            'rca_file' => 'RCA',
            'mop_file' => 'MOP',
        ];
        $rows = [];
        $legacyTotal = 0;
        $legacyAvailable = 0;
        $legacyMissing = 0;

        MCase::select(array_merge(['id_case'], array_keys($legacyFields)))
            ->orderBy('id_case')
            ->chunkById(200, function ($cases) use ($legacyFields, &$legacyTotal, &$legacyAvailable, &$legacyMissing) {
                foreach ($cases as $case) {
                    foreach ($legacyFields as $field => $label) {
                        $path = $case->{$field};
                        if (!$path) {
                            continue;
                        }

                        $legacyTotal++;
                        if (Storage::disk('public')->exists($path)) {
                            $legacyAvailable++;
                        } else {
                            $legacyMissing++;
                            $this->warn('File lama tidak ditemukan: case ' . $case->id_case . ' · ' . $label . ' · ' . $path);
                        }
                    }
                }
            }, 'id_case');

        $newAvailable = 0;
        $newFailed = 0;
        if (Schema::hasTable('case_documents')) {
            $newAvailable = CaseDocument::where('status', 'available')->count();
            $newFailed = CaseDocument::where('status', 'failed')->count();
        }

        $rows[] = ['Legacy terdaftar', $legacyTotal];
        $rows[] = ['Legacy tersedia', $legacyAvailable];
        $rows[] = ['Legacy hilang', $legacyMissing];
        $rows[] = ['Dokumen baru tersedia', $newAvailable];
        $rows[] = ['Upload baru gagal', $newFailed];

        $this->table(['Status', 'Jumlah'], $rows);
        $this->info('Audit selesai. Tidak ada file atau data yang diubah.');

        return $legacyMissing > 0 ? 1 : 0;
    }
}
