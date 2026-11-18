<?php

namespace Tests\Unit;

use App\CaseDocument;
use App\MCase;
use App\Services\CaseDocumentStorage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class CaseDocumentStorageTest extends TestCase
{
    public function test_case_document_names_follow_the_required_format()
    {
        $case = new MCase();
        $case->setAttribute('id_case', 129);
        $case->subject = 'Access Point Offline / Gedung Utama';
        $file = UploadedFile::fake()->image('bukti lapangan.jpg');
        $service = new CaseDocumentStorage();

        $date = now()->format('Ymd');

        $this->assertSame(
            $date . '_evidance_access_point_offline_gedung_utama_129.jpg',
            $service->storedName($case, CaseDocument::TYPE_EVIDENCE, $file)
        );
        $this->assertSame(
            $date . '_RCA_access_point_offline_gedung_utama_129_v2.jpg',
            $service->storedName($case, CaseDocument::TYPE_RCA, $file, 2)
        );
    }

    public function test_long_subject_is_limited_for_safe_storage_paths()
    {
        $case = new MCase();
        $case->setAttribute('id_case', 88);
        $case->subject = str_repeat('Subject Project Sangat Panjang ', 10);
        $file = UploadedFile::fake()->create('rca.pdf', 100, 'application/pdf');

        $name = (new CaseDocumentStorage())->storedName($case, CaseDocument::TYPE_RCA, $file);

        $this->assertLessThan(150, strlen($name));
        $this->assertStringEndsWith('_88.pdf', $name);
    }
}
