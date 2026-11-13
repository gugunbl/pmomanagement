<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (!auth()->user()->can('menu-dashboard')) {
            return redirect()->route('home_user');
        }
// hari ini
$today = Carbon::today();

// batas akhir = akhir bulan + 5 bulan dari hari ini
$endLimit = Carbon::today()
    ->addMonthsNoOverflow(5)
    ->endOfMonth();

$maintenanceEndingSoon = DB::table('m_project')
    ->select('id_project', 'customer', 'nama_project', 'tanggal_akhir_maintenance')
    ->where('status_maintenance', 1)
    ->whereBetween('tanggal_akhir_maintenance', [$today, $endLimit])
    ->orderBy('tanggal_akhir_maintenance', 'asc')
    ->get();
    
    
    $totalPerTahun = DB::table('m_project')
    ->select('tahun', DB::raw('count(*) as total'))
    ->whereNotNull('tahun')
    ->groupBy('tahun')
    ->orderBy('tahun')
    ->get();
    // KPI
    $totalImplementasiAktif = DB::table('m_project')
        ->where('status_implementation', 1)
        ->count();

    $totalMaintenanceAktif = DB::table('m_project')
        ->where('status_maintenance', 1)
        ->count();

    $totalBAMaintenanceOnProgress = DB::table('project_top_maintenance')
        ->where('status', '!=', 'paid')
        ->where('periode_end', '<=', Carbon::today()->format('Y-m-d'))
        ->count();

    // DATA CHART (Stacked Column)
    $chartRaw = DB::table('m_project')
        ->select('tahun', 'customer', DB::raw('count(*) as total'))
        ->whereNotNull('tahun')
        ->groupBy('tahun', 'customer')
        ->orderBy('tahun')
        ->get();

    // Olah data untuk Chart.js
    $years = $chartRaw->pluck('tahun')->unique()->values();
    $customers = $chartRaw->pluck('customer')->unique()->values();

    $datasets = [];
    $customerColors = [
        'BRI' => '#00529C',
        'PNM' => '#FF8000',
        'BJB' => '#00A38D',
        'Askrindo' => '#F2A100',
        'PT. Timah' => '#53565A',
        'Allo Bank' => '#6F2DBD',
        'Bank DKI' => '#ED1C24',
        'Jamkrindo Syariah' => '#008800',
        'PT Timah' => '#53565A', // Handle variations if any
    ];

    $palette = ['#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf'];
    $colorIndex = 0;

    foreach ($customers as $customer) {
        $color = $customerColors[$customer] ?? $palette[$colorIndex % count($palette)];
        if (!isset($customerColors[$customer])) {
            $colorIndex++;
        }

        $datasets[] = [
            'label' => $customer,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'data' => $years->map(function ($year) use ($chartRaw, $customer) {
                return $chartRaw
                    ->where('tahun', $year)
                    ->where('customer', $customer)
                    ->sum('total');
            }),
        ];
    }

    // TABLE MAINTENANCE PER CUSTOMER
    $maintenanceByCustomer = DB::table('m_project')
        ->select('customer', DB::raw('count(*) as total'))
        ->where('status_maintenance', 1)
        ->groupBy('customer')
        ->orderByDesc('total')
        ->get();

    return view('home', compact(
        'totalImplementasiAktif',
        'totalMaintenanceAktif',
        'totalBAMaintenanceOnProgress',
        'years',
        'datasets',
        'maintenanceByCustomer',
        'totalPerTahun',
        'maintenanceEndingSoon'
    ));
    }

    public function homeUser()
    {
        $user = auth()->user();
        $now = Carbon::now();

        // Get project IDs mapped to the logged-in user
        $userProjectIds = DB::table('project_user')
            ->where('user_id', $user->id)
            ->pluck('id_project')
            ->toArray();

        // Base query builder for cases (filtered by user's projects if any)
        $caseQuery = DB::table('m_cases');
        if (count($userProjectIds) > 0) {
            $caseQuery->whereIn('id_project', $userProjectIds);
        }

        // 1. Tiket Bulan Lalu - tickets opened last month (any status)
        $tiketBulanLalu = (clone $caseQuery)
            ->whereYear('open_date', $now->copy()->subMonth()->year)
            ->whereMonth('open_date', $now->copy()->subMonth()->month)
            ->count();

        // 2. Tiket Bulan Ini - tickets opened this month (any status)
        $tiketBulanIni = (clone $caseQuery)
            ->whereYear('open_date', $now->year)
            ->whereMonth('open_date', $now->month)
            ->count();

        // 3. Tiket Open - tickets with status Open or In Progress (any date)
        $tiketOpen = (clone $caseQuery)
            ->whereIn('status', ['Open', 'In Progress'])
            ->count();

        // 4. Tiket Closed Bulan Ini - tickets closed this month
        $tiketClosedBulanIni = (clone $caseQuery)
            ->where('status', 'Closed')
            ->whereYear('closed_date', $now->year)
            ->whereMonth('closed_date', $now->month)
            ->count();

        // 5. Project Maintenance Active (same as admin dashboard)
        $projectMaintenanceActive = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->count();

        // 6. Manage Service - maintenance active + tipe = Manage Service
        $manageService = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->where('tipe_maintenance', 'Manage Service')
            ->count();

        // 7. PM CM - maintenance active + tipe = PM-CM
        $pmCm = DB::table('m_project')
            ->where('status_maintenance', 1)
            ->where('tipe_maintenance', 'PM-CM')
            ->count();

        // ========== CHART: Daily trend last 30 days ==========
        $startDate = $now->copy()->subDays(29)->startOfDay();
        $endDate = $now->copy()->endOfDay();

        // Generate all 30 days labels
        $chartLabels = [];
        $chartTotalTickets = [];
        $chartClosedTickets = [];

        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        // Get daily opened tickets (last 30 days)
        $openedByDay = (clone $caseQuery)
            ->whereBetween('open_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(DB::raw('DATE(open_date) as the_date'), DB::raw('COUNT(*) as total'))
            ->groupBy('the_date')
            ->pluck('total', 'the_date')
            ->toArray();

        // Get daily closed tickets (last 30 days)
        $closedByDay = (clone $caseQuery)
            ->whereBetween('closed_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('status', 'Closed')
            ->select(DB::raw('DATE(closed_date) as the_date'), DB::raw('COUNT(*) as total'))
            ->groupBy('the_date')
            ->pluck('total', 'the_date')
            ->toArray();

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $chartLabels[] = $date->format('d M');
            $chartTotalTickets[] = $openedByDay[$dateStr] ?? 0;
            $chartClosedTickets[] = $closedByDay[$dateStr] ?? 0;
        }

        // ========== TOP 10 PROJECT, TICKET OPEN ==========
        $top10Query = DB::table('m_cases')
            ->join('m_project', 'm_cases.id_project', '=', 'm_project.id_project')
            ->select(
                'm_project.id_project',
                'm_project.nama_project',
                'm_project.tipe_maintenance',
                DB::raw("SUM(CASE WHEN m_cases.status IN ('Open', 'In Progress') THEN 1 ELSE 0 END) as open_count"),
                DB::raw("SUM(CASE WHEN m_cases.status = 'Closed' THEN 1 ELSE 0 END) as closed_count")
            );

        if (count($userProjectIds) > 0) {
            $top10Query->whereIn('m_cases.id_project', $userProjectIds);
        }

        $top10Projects = $top10Query
            ->groupBy('m_project.id_project', 'm_project.nama_project', 'm_project.tipe_maintenance')
            ->orderByDesc('open_count')
            ->limit(10)
            ->get();

        return view('home_user', compact(
            'tiketBulanLalu',
            'tiketBulanIni',
            'tiketOpen',
            'tiketClosedBulanIni',
            'projectMaintenanceActive',
            'manageService',
            'pmCm',
            'chartLabels',
            'chartTotalTickets',
            'chartClosedTickets',
            'top10Projects'
        ));
    }

}
