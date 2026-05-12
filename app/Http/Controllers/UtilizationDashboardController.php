<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UtilizationDashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee_name = $request->get('employee_name');
        $report_date = $request->get('report_date');

        // Query for Tab 1: Dashboard Chart
        $query1 = DB::table('daily_task_reports as dtr')
            ->select(
                'dtr.id',
                'dtr.employee_name',
                'dtr.report_date',
                'dtr.day_name',
                'dtr.total_productive_time',
                'dtr.work_utilization_percentage',
                DB::raw('COUNT(ta.id) as total_activities')
            )
            ->leftJoin('task_activities as ta', 'dtr.id', '=', 'ta.report_id')
            ->groupBy('dtr.id', 'dtr.employee_name', 'dtr.report_date', 'dtr.day_name', 'dtr.total_productive_time', 'dtr.work_utilization_percentage')
            ->orderBy('dtr.report_date', 'asc');

        if ($employee_name) {
            $query1->where('dtr.employee_name', $employee_name);
        }
        if ($report_date) {
            $query1->where('dtr.report_date', $report_date);
        }

        $reports = $query1->get();

        // Query for Tab 2: Detail Grid
        $query2 = DB::table('daily_task_reports as dtr')
            ->select(
                'dtr.employee_name',
                'dtr.report_date',
                'ta.start_time',
                'ta.end_time',
                'ta.activity_description',
                'ta.duration_minutes'
            )
            ->join('task_activities as ta', 'dtr.id', '=', 'ta.report_id')
            ->orderBy('dtr.report_date', 'desc')
            ->orderBy('ta.start_time', 'desc');

        if ($employee_name) {
            $query2->where('dtr.employee_name', $employee_name);
        }
        if ($report_date) {
            $query2->where('dtr.report_date', $report_date);
        }

        $activities = $query2->paginate(20)->appends($request->all());

        // For filter options
        $employees = DB::table('daily_task_reports')->distinct()->pluck('employee_name');

        return view('dashboard.utilisasi', compact('reports', 'activities', 'employees'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:5120', // Max 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            
            // Explicitly use the 'public' disk so it goes to storage/app/public/task_reports
            $path = $file->storeAs('task_reports', $filename, 'public');
            
            // Resolve the absolute path correctly for the public disk
            $fullPath = storage_path('app/public/' . $path);
            
            // Call Gemini Vision Service
            $aiService = new \App\Services\AI\GeminiVisionService();
            $result = $aiService->extractFromImage($fullPath);
            
            if ($result['success'] && $result['data']) {
                $data = $result['data'];
                
                // Insert into daily_task_reports
                $reportId = DB::table('daily_task_reports')->insertGetId([
                    'employee_name' => $data['employee_name'] ?? 'Unknown',
                    'report_date' => $data['report_date'] ?? date('Y-m-d'),
                    'day_name' => $data['day_name'] ?? null,
                    'total_productive_time' => $data['total_productive_time'] ?? null,
                    'work_utilization_percentage' => $data['work_utilization_percentage'] ?? null,
                    'effective_work_hours' => $data['effective_work_hours'] ?? 8,
                    'image_path' => $path,
                    'ocr_method' => 'gemini',
                    'ai_confidence_score' => $result['confidence'],
                    'raw_ai_response' => json_encode($result['raw_response']),
                    'extracted_entities' => json_encode($data),
                    'status' => 'ai_parsed',
                    'processed_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Insert activities
                if (!empty($data['activities']) && is_array($data['activities'])) {
                    $activitiesToInsert = [];
                    foreach ($data['activities'] as $activity) {
                        // Calculate duration if start and end time exist
                        $duration = null;
                        if (!empty($activity['start_time']) && !empty($activity['end_time'])) {
                            $start = \Carbon\Carbon::parse($activity['start_time']);
                            $end = \Carbon\Carbon::parse($activity['end_time']);
                            $duration = $start->diffInMinutes($end);
                        }

                        $activitiesToInsert[] = [
                            'report_id' => $reportId,
                            'start_time' => $activity['start_time'] ?? '00:00',
                            'end_time' => $activity['end_time'] ?? '00:00',
                            'activity_description' => $activity['description'] ?? '',
                            'duration_minutes' => $duration,
                            'is_parallel' => $activity['is_parallel'] ?? false,
                            'created_at' => now(),
                        ];
                    }
                    if (count($activitiesToInsert) > 0) {
                        DB::table('task_activities')->insert($activitiesToInsert);
                    }
                }
                
                return redirect()->back()->with('success', 'Screenshot berhasil diproses oleh Gemini AI. Data telah ditambahkan.');
            } else {
                return redirect()->back()->with('error', 'Gagal memproses gambar dengan AI: ' . ($result['error'] ?? 'Unknown error'));
            }
        }

        return redirect()->back()->with('error', 'Gagal mengunggah file.');
    }
}
