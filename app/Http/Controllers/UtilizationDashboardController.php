<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessDailyTaskReportImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UtilizationDashboardController extends Controller
{
    public function index(Request $request)
    {
        $employee_names = $request->get('employee_name', []);
        $start_date = $request->get('start_date');
        $end_date   = $request->get('end_date');

        // Default: last 30 days when no date filter is explicitly set
        if (!$start_date && !$end_date && !$request->hasAny(['start_date', 'end_date'])) {
            $start_date = \Carbon\Carbon::now()->subDays(29)->toDateString();
        }

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

        if (!empty($employee_names)) {
            $query1->whereIn('dtr.employee_name', $employee_names);
        }
        if ($start_date && $end_date) {
            $query1->whereBetween('dtr.report_date', [$start_date, $end_date]);
        } elseif ($start_date) {
            $query1->where('dtr.report_date', '>=', $start_date);
        } elseif ($end_date) {
            $query1->where('dtr.report_date', '<=', $end_date);
        }

        $reportsForChart = (clone $query1)->get();
        $reports = $query1->paginate(20, ['*'], 'reports_page')->appends($request->all());

        // Query for Tab 2: Weekly Trend (aggregated per week per employee)
        $query_weekly = DB::table('daily_task_reports as dtr')
            ->select(
                'dtr.employee_name',
                DB::raw("YEAR(dtr.report_date) as report_year"),
                DB::raw("WEEK(dtr.report_date, 3) as report_week"),
                DB::raw("MIN(dtr.report_date) as week_start"),
                DB::raw("MAX(dtr.report_date) as week_end"),
                DB::raw("ROUND(AVG(dtr.work_utilization_percentage), 2) as avg_utilization"),
                DB::raw("SUM(TIME_TO_SEC(dtr.total_productive_time)) as total_productive_seconds"),
                DB::raw("COUNT(dtr.id) as total_reports")
            )
            ->groupBy('dtr.employee_name', DB::raw('YEAR(dtr.report_date)'), DB::raw('WEEK(dtr.report_date, 3)'))
            ->orderBy(DB::raw('YEAR(dtr.report_date)'), 'asc')
            ->orderBy(DB::raw('WEEK(dtr.report_date, 3)'), 'asc');

        if (!empty($employee_names)) {
            $query_weekly->whereIn('dtr.employee_name', $employee_names);
        }
        if ($start_date && $end_date) {
            $query_weekly->whereBetween('dtr.report_date', [$start_date, $end_date]);
        } elseif ($start_date) {
            $query_weekly->where('dtr.report_date', '>=', $start_date);
        } elseif ($end_date) {
            $query_weekly->where('dtr.report_date', '<=', $end_date);
        }

        $weeklyReports = $query_weekly->get();

        // Fetch activity counts separately to avoid GROUP BY issues with correlated subqueries
        $activities_weekly_query = DB::table('task_activities as ta')
            ->join('daily_task_reports as dtr', 'ta.report_id', '=', 'dtr.id')
            ->select(
                'dtr.employee_name',
                DB::raw("YEAR(dtr.report_date) as report_year"),
                DB::raw("WEEK(dtr.report_date, 3) as report_week"),
                DB::raw("COUNT(ta.id) as total_activities")
            )
            ->groupBy('dtr.employee_name', DB::raw('YEAR(dtr.report_date)'), DB::raw('WEEK(dtr.report_date, 3)'));

        if (!empty($employee_names)) {
            $activities_weekly_query->whereIn('dtr.employee_name', $employee_names);
        }
        if ($start_date && $end_date) {
            $activities_weekly_query->whereBetween('dtr.report_date', [$start_date, $end_date]);
        } elseif ($start_date) {
            $activities_weekly_query->where('dtr.report_date', '>=', $start_date);
        } elseif ($end_date) {
            $activities_weekly_query->where('dtr.report_date', '<=', $end_date);
        }

        $activities_weekly_data = $activities_weekly_query->get();

        // Format and merge data
        foreach ($weeklyReports as $wr) {
            // Find matching activity count
            $match = $activities_weekly_data->filter(function($item) use ($wr) {
                return $item->employee_name == $wr->employee_name && 
                       $item->report_year == $wr->report_year && 
                       $item->report_week == $wr->report_week;
            })->first();
            
            $wr->total_activities = $match ? $match->total_activities : 0;

            $totalSec = (int) $wr->total_productive_seconds;
            $hours = floor($totalSec / 3600);
            $minutes = floor(($totalSec % 3600) / 60);
            $wr->total_productive_formatted = sprintf('%02d:%02d', $hours, $minutes);
        }

        // Query for Tab 3: Detail Grid
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

        if (!empty($employee_names)) {
            $query2->whereIn('dtr.employee_name', $employee_names);
        }
        if ($start_date && $end_date) {
            $query2->whereBetween('dtr.report_date', [$start_date, $end_date]);
        } elseif ($start_date) {
            $query2->where('dtr.report_date', '>=', $start_date);
        } elseif ($end_date) {
            $query2->where('dtr.report_date', '<=', $end_date);
        }

        $activities = $query2->paginate(20)->appends($request->all());

        // For filter options
        $employees = DB::table('daily_task_reports')->distinct()->pluck('employee_name');

        // Data for High Level Report
        $highLevelWeeks = $weeklyReports->groupBy(function($item) {
            return $item->report_year . '-' . str_pad($item->report_week, 2, '0', STR_PAD_LEFT);
        })->keys()->sort();

        $highLevelData = [];
        $employeeAverages = [];

        $targetEmployees = !empty($employee_names) ? $employee_names : $employees;

        foreach ($targetEmployees as $emp) {
            $empData = [];
            $totalUtil = 0;
            $countUtil = 0;

            foreach ($highLevelWeeks as $yw) {
                list($year, $week) = explode('-', $yw);
                $report = $weeklyReports->first(function($item) use ($emp, $year, $week) {
                    return $item->employee_name == $emp && 
                           $item->report_year == $year && 
                           $item->report_week == (int)$week;
                });

                $util = $report ? $report->avg_utilization : 0;
                $empData[$yw] = $util;
                
                if ($report) {
                    $totalUtil += $util;
                    $countUtil++;
                }
            }

            $highLevelData[$emp] = $empData;
            $employeeAverages[$emp] = $countUtil > 0 ? round($totalUtil / $countUtil, 2) : 0;
        }

        // Get week labels for headers
        $weekLabels = [];
        foreach ($highLevelWeeks as $yw) {
            list($year, $week) = explode('-', $yw);
            $sample = $weeklyReports->first(function($item) use ($year, $week) {
                return $item->report_year == $year && $item->report_week == (int)$week;
            });
            $weekLabels[$yw] = [
                'week_num' => (int)$week,
                'year' => $year,
                'start' => $sample ? date('d M', strtotime($sample->week_start)) : '',
                'end' => $sample ? date('d M', strtotime($sample->week_end)) : ''
            ];
        }

        return view('dashboard.utilisasi', compact('reports', 'reportsForChart', 'weeklyReports', 'activities', 'employees', 'targetEmployees', 'highLevelData', 'highLevelWeeks', 'employeeAverages', 'weekLabels', 'start_date', 'end_date'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if (!$request->hasFile('file')) {
            return redirect()->back()->with('error', 'Gagal mengunggah file.');
        }

        $file     = $request->file('file');
        $path = $file->store('task_reports', 'public');
        $importId = DB::table('daily_task_report_imports')->insertGetId([
            'user_id' => auth()->id(),
            'file_path' => $path,
            'status' => 'queued',
            'message' => 'Upload diterima dan menunggu worker.',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        try {
            ProcessDailyTaskReportImport::dispatch($path, $importId)->onConnection('database');
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($path);
            DB::table('daily_task_report_imports')->where('id', $importId)->update([
                'status' => 'failed',
                'message' => 'Upload gagal dimasukkan ke antrean. Silakan coba lagi.',
                'read_at' => now(),
                'updated_at' => now(),
            ]);
            Log::error('Failed to queue daily task report import: ' . $e->getMessage());

            return redirect()->back()->with('error', 'Gagal memasukkan upload ke antrean pemrosesan. Silakan coba lagi.');
        }

        return redirect()->back();
    }

    public function importStatuses()
    {
        $imports = DB::table('daily_task_report_imports')
            ->where('user_id', auth()->id())
            ->where(function ($query) {
                $query->whereIn('status', ['queued', 'processing'])
                    ->orWhereNull('read_at');
            })
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'status', 'message', 'inserted_days', 'inserted_activities', 'created_at', 'updated_at']);

        return response()->json(['imports' => $imports]);
    }

    public function markImportStatusRead($id)
    {
        $updated = DB::table('daily_task_report_imports')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->whereIn('status', ['completed', 'failed'])
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => $updated > 0]);
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|mimes:xlsx,xls|max:5120',
        ]);

        try {
            $file = $request->file('file_excel');

            // Use PhpSpreadsheet to read the file
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true); // indexed by column letter

            // --- Parse Employee Name (Row 1, Col A) ---
            $employeeName = trim($rows[1]['A'] ?? 'Unknown');
            // Remove "Date :" or any header-like prefix if it's on a different row
            if (empty($employeeName) || strtolower($employeeName) === 'date :') {
                $employeeName = 'Unknown';
            }

            // --- Group data rows by date ---
            // Skip row 1 (header) and row 2 ("Date :" label)
            $dateGroups = []; // [ 'Y-m-d' => [ 'day_name' => ..., 'activities' => [...] ] ]

            $indonesianMonths = [
                'januari' => '01', 'februari' => '02', 'maret' => '03',
                'april' => '04', 'mei' => '05', 'juni' => '06',
                'juli' => '07', 'agustus' => '08', 'september' => '09',
                'oktober' => '10', 'november' => '11', 'desember' => '12',
            ];

            $currentDate = null;
            $currentDayName = null;

            foreach ($rows as $rowIndex => $row) {
                // Skip header rows (1 and 2)
                if ($rowIndex <= 2) continue;

                $colA = trim($row['A'] ?? '');
                $colB = trim($row['B'] ?? '');
                $colD = trim($row['D'] ?? ''); // Waktu (e.g. "80 menit")
                $colE = $row['E'] ?? null;      // Durasi (jam) numeric

                // Skip fully empty rows
                if (empty($colA) && empty($colB)) continue;

                // Check if col A contains a date (e.g. "Senin, 04 Mei 2026")
                if (!empty($colA) && preg_match('/(\w+),\s*(\d{1,2})\s+(\w+)\s+(\d{4})/u', $colA, $matches)) {
                    $dayName   = trim($matches[1]);
                    $day       = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $monthName = strtolower($matches[3]);
                    $year      = $matches[4];

                    $month = $indonesianMonths[$monthName] ?? '01';
                    $currentDate    = "{$year}-{$month}-{$day}";
                    $currentDayName = $dayName;

                    if (!isset($dateGroups[$currentDate])) {
                        $dateGroups[$currentDate] = [
                            'day_name'   => $currentDayName,
                            'activities' => [],
                        ];
                    }
                }

                // Parse activity from col B
                if (!empty($colB) && !empty($currentDate)) {
                    // Parse duration in minutes from col D (e.g. "80 menit" or just "80")
                    $durationMinutes = null;
                    if (!empty($colD)) {
                        preg_match('/(\d+(?:[.,]\d+)?)/', $colD, $dm);
                        $durationMinutes = isset($dm[1]) ? (int) str_replace(',', '.', $dm[1]) : null;
                    }

                    // Durasi in hours from col E
                    $durationHours = null;
                    if ($colE !== null && $colE !== '') {
                        $durationHours = (float) str_replace(',', '.', $colE);
                        // Derive duration minutes from hours if not found in col D
                        if ($durationMinutes === null && $durationHours > 0) {
                            $durationMinutes = (int) round($durationHours * 60);
                        }
                    }

                    $dateGroups[$currentDate]['activities'][] = [
                        'description'      => $colB,
                        'duration_minutes' => $durationMinutes,
                        'duration_hours'   => $durationHours,
                    ];
                }
            }

            if (empty($dateGroups)) {
                return redirect()->back()->with('error', 'Tidak ada data yang dapat dibaca dari file Excel. Pastikan format template sesuai.');
            }

            // --- Insert into DB ---
            $insertedDays       = 0;
            $insertedActivities = 0;

            foreach ($dateGroups as $date => $group) {
                if (empty($group['activities'])) continue;

                // Calculate total duration in minutes
                $totalMinutes = array_sum(array_column($group['activities'], 'duration_minutes'));
                $effectiveHours = 8;
                $utilizationPct = $effectiveHours > 0
                    ? round(($totalMinutes / 60) / $effectiveHours * 100, 2)
                    : 0;

                $hours   = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $totalProductiveTime = sprintf('%02d:%02d:00', $hours, $minutes);

                // Avoid duplicate: check if report already exists for same employee + date
                $exists = DB::table('daily_task_reports')
                    ->where('employee_name', $employeeName)
                    ->where('report_date', $date)
                    ->exists();

                if ($exists) continue;

                $reportId = DB::table('daily_task_reports')->insertGetId([
                    'employee_name'              => $employeeName,
                    'report_date'                => $date,
                    'day_name'                   => $group['day_name'],
                    'total_productive_time'      => $totalProductiveTime,
                    'work_utilization_percentage'=> $utilizationPct,
                    'effective_work_hours'       => $effectiveHours,
                    'ocr_method'                 => 'excel',
                    'status'                     => 'validated',
                    'processed_at'               => now(),
                    'created_at'                 => now(),
                    'updated_at'                 => now(),
                ]);

                $activitiesToInsert = [];
                foreach ($group['activities'] as $act) {
                    $activitiesToInsert[] = [
                        'report_id'            => $reportId,
                        'start_time'           => '00:00',
                        'end_time'             => '00:00',
                        'activity_description' => $act['description'],
                        'duration_minutes'     => $act['duration_minutes'],
                        'is_parallel'          => false,
                        'created_at'           => now(),
                    ];
                    $insertedActivities++;
                }

                DB::table('task_activities')->insert($activitiesToInsert);
                $insertedDays++;
            }

            if ($insertedDays === 0) {
                return redirect()->back()->with('warning', 'Semua data sudah ada di database (duplikat dilewati).');
            }

            return redirect()->back()->with('success', "Import Excel berhasil! {$insertedDays} hari & {$insertedActivities} aktivitas telah ditambahkan atas nama: {$employeeName}.");

        } catch (\Exception $e) {
            Log::error('Excel Import Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses file Excel: ' . $e->getMessage());
        }
    }
}
