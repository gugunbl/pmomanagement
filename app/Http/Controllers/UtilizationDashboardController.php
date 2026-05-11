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
}
