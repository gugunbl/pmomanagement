<?php

namespace App\Services;

use App\CaseDocument;
use App\MCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CaseDocumentStorage
{
    public function store(MCase $case, $type, UploadedFile $file, $userId = null)
    {
        $disk = config('filesystems.case_document_disk', 'public');
        $version = ((int) $case->documents()->where('document_type', $type)->max('version')) + 1;
        $storedName = $this->storedName($case, $type, $file, $version);
        $checksum = @hash_file('sha256', $file->getRealPath()) ?: null;

        $document = CaseDocument::create([
            'case_id' => $case->id_case,
            'document_type' => $type,
            'disk' => $disk,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'checksum' => $checksum,
            'version' => $version,
            'is_active' => false,
            'status' => 'pending',
            'uploaded_by' => $userId,
        ]);

        $path = null;

        try {
            if ($disk === 'nextcloud') {
                $case->loadMissing('project');
                if (!$case->project) {
                    throw new NextcloudWebDavException('Project untuk case ini tidak ditemukan.');
                }

                $nextcloud = app(NextcloudWebDav::class);
                $nextcloud->ensureProjectFolders($case->project->link_dokumen);
                $path = $nextcloud->caseDocumentPath($case->project, $storedName);
                $nextcloud->upload($file->getRealPath(), $path);
            } else {
                $directory = 'cases/documents/' . $case->id_case;
                $path = $file->storeAs($directory, $storedName, $disk);
                if (!$path) {
                    throw new \RuntimeException('File tidak dapat disimpan ke storage lokal.');
                }
            }

            DB::transaction(function () use ($case, $type, $document, $path) {
                CaseDocument::where('case_id', $case->id_case)
                    ->where('document_type', $type)
                    ->where('id', '<>', $document->id)
                    ->update(['is_active' => false]);

                $document->update([
                    'path' => $path,
                    'status' => 'available',
                    'is_active' => true,
                    'error_message' => null,
                ]);
            });
        } catch (\Throwable $e) {
            if ($path) {
                $this->deleteUploadedFile($disk, $path, $document->id);
            }

            $document->update([
                'path' => $path,
                'status' => 'failed',
                'is_active' => false,
                'error_message' => Str::limit($e->getMessage(), 1000, ''),
            ]);

            Log::warning('Case document upload failed.', [
                'case_id' => $case->id_case,
                'document_id' => $document->id,
                'document_type' => $type,
                'disk' => $disk,
                'message' => $e->getMessage(),
            ]);
        }

        return $document->fresh();
    }

    public function storedName(MCase $case, $type, UploadedFile $file, $version = 1)
    {
        $labels = [
            CaseDocument::TYPE_EVIDENCE => 'evidance',
            CaseDocument::TYPE_RCA => 'RCA',
            CaseDocument::TYPE_MOP => 'MOP',
        ];

        if (!isset($labels[$type])) {
            throw new \InvalidArgumentException('Tipe dokumen case tidak dikenal.');
        }

        $subject = Str::slug(Str::limit((string) $case->subject, 80, ''), '_');
        $subject = trim(mb_substr($subject ?: 'case', 0, 70), '_');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $versionSuffix = $version > 1 ? '_v' . $version : '';

        return now()->format('Ymd')
            . '_' . $labels[$type]
            . '_' . $subject
            . '_' . $case->id_case
            . $versionSuffix
            . '.' . $extension;
    }

    private function deleteUploadedFile($disk, $path, $documentId)
    {
        try {
            if ($disk === 'nextcloud') {
                app(NextcloudWebDav::class)->delete($path);
            } else {
                Storage::disk($disk)->delete($path);
            }
        } catch (\Throwable $cleanupException) {
            Log::warning('Case document rollback cleanup failed.', [
                'document_id' => $documentId,
                'disk' => $disk,
                'path' => $path,
                'message' => $cleanupException->getMessage(),
            ]);
        }
    }
}
