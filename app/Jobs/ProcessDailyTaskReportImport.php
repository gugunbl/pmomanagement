<?php

namespace App\Jobs;

use App\Services\AI\GeminiVisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessDailyTaskReportImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public $timeout = 180;

    protected $path;

    protected $importId;

    public function __construct(string $path, int $importId)
    {
        $this->path = $path;
        $this->importId = $importId;
    }

    public function handle(GeminiVisionService $aiService)
    {
        DB::table('daily_task_report_imports')->where('id', $this->importId)->update([
            'status' => 'processing',
            'message' => 'Gambar sedang dianalisis oleh Gemini AI.',
            'updated_at' => now(),
        ]);

        $fullPath = storage_path('app/public/' . $this->path);

        if (!is_file($fullPath)) {
            throw new \RuntimeException('Uploaded report image is missing: ' . $this->path);
        }

        $result = $aiService->extractFromImage($fullPath);

        if (empty($result['success']) || empty($result['data'])) {
            throw new \RuntimeException($result['error'] ?? 'Gemini returned no report data.');
        }

        $data = $result['data'];
        $employeeName = $data['employee_name'] ?? 'Unknown';

        if (!empty($data['entries']) && is_array($data['entries'])) {
            $entries = $data['entries'];
        } else {
            $entries = [[
                'report_date' => $data['report_date'] ?? date('Y-m-d'),
                'day_name' => $data['day_name'] ?? null,
                'activities' => $data['activities'] ?? [],
            ]];
        }

        $insertedDays = 0;
        $insertedActivities = 0;

        foreach ($entries as $entry) {
            $reportDate = $entry['report_date'] ?? date('Y-m-d');
            $activities = $entry['activities'] ?? [];

            if (empty($activities)) {
                continue;
            }

            $exists = DB::table('daily_task_reports')
                ->where('employee_name', $employeeName)
                ->where('report_date', $reportDate)
                ->exists();

            if ($exists) {
                continue;
            }

            $activitiesToInsert = [];
            $totalMinutes = 0;

            foreach ($activities as $activity) {
                $duration = $activity['duration_minutes'] ?? null;

                if ($duration === null && !empty($activity['start_time']) && !empty($activity['end_time'])) {
                    try {
                        $start = \Carbon\Carbon::parse($activity['start_time']);
                        $end = \Carbon\Carbon::parse($activity['end_time']);
                        $duration = abs($start->diffInMinutes($end));
                    } catch (\Exception $e) {
                        $duration = null;
                    }
                }

                $totalMinutes += (int) ($duration ?? 0);
                $activitiesToInsert[] = [
                    'start_time' => $activity['start_time'] ?? '00:00',
                    'end_time' => $activity['end_time'] ?? '00:00',
                    'activity_description' => $activity['description'] ?? '',
                    'duration_minutes' => $duration,
                    'is_parallel' => $activity['is_parallel'] ?? false,
                    'created_at' => now(),
                ];
            }

            $effectiveHours = 8;
            $utilizationPct = $totalMinutes > 0
                ? round(($totalMinutes / 60) / $effectiveHours * 100, 2)
                : ($data['work_utilization_percentage'] ?? null);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;
            $totalProductiveTime = $totalMinutes > 0
                ? sprintf('%02d:%02d:00', $hours, $minutes)
                : ($data['total_productive_time'] ?? null);

            DB::transaction(function () use (
                $employeeName,
                $reportDate,
                $entry,
                $totalProductiveTime,
                $utilizationPct,
                $effectiveHours,
                $result,
                $data,
                $activitiesToInsert
            ) {
                $reportId = DB::table('daily_task_reports')->insertGetId([
                    'employee_name' => $employeeName,
                    'report_date' => $reportDate,
                    'day_name' => $entry['day_name'] ?? null,
                    'total_productive_time' => $totalProductiveTime,
                    'work_utilization_percentage' => $utilizationPct,
                    'effective_work_hours' => $effectiveHours,
                    'image_path' => $this->path,
                    'ocr_method' => 'gemini',
                    'ai_confidence_score' => $result['confidence'] ?? null,
                    'raw_ai_response' => json_encode($result['raw_response'] ?? []),
                    'extracted_entities' => json_encode($data),
                    'status' => 'ai_parsed',
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($activitiesToInsert as &$activity) {
                    $activity['report_id'] = $reportId;
                }
                unset($activity);

                DB::table('task_activities')->insert($activitiesToInsert);
            });

            $insertedDays++;
            $insertedActivities += count($activitiesToInsert);
        }

        $message = $insertedDays > 0
            ? "Berhasil mengimpor {$insertedDays} hari dan {$insertedActivities} aktivitas untuk {$employeeName}."
            : 'Proses selesai, tetapi tidak ada data baru. Tanggal mungkin duplikat atau aktivitas tidak ditemukan.';

        DB::table('daily_task_report_imports')->where('id', $this->importId)->update([
            'status' => 'completed',
            'message' => $message,
            'inserted_days' => $insertedDays,
            'inserted_activities' => $insertedActivities,
            'updated_at' => now(),
        ]);

        Log::info('Daily task report import completed.', [
            'import_id' => $this->importId,
            'image_path' => $this->path,
            'employee_name' => $employeeName,
            'inserted_days' => $insertedDays,
            'inserted_activities' => $insertedActivities,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        DB::table('daily_task_report_imports')->where('id', $this->importId)->update([
            'status' => 'failed',
            'message' => 'Gagal memproses laporan dengan AI. Silakan coba lagi beberapa saat lagi.',
            'updated_at' => now(),
        ]);

        Log::error('Daily task report import job failed.', [
            'import_id' => $this->importId,
            'image_path' => $this->path,
            'error' => $exception->getMessage(),
        ]);
    }
}