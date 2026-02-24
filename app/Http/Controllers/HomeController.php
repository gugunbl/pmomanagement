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
// hari ini
$today = Carbon::today();

// batas akhir = akhir bulan + 5 bulan dari hari ini
$endLimit = Carbon::today()
    ->addMonthsNoOverflow(5)
    ->endOfMonth();

$maintenanceEndingSoon = DB::table('m_project')
    ->select('customer', 'nama_project', 'tanggal_akhir_maintenance')
    ->where('status_maintenance', 1)
    ->whereBetween('tanggal_akhir_maintenance', [$today, $endLimit])
    ->orderBy('tanggal_akhir_maintenance', 'asc')
    ->get();
    
    
    $totalPerTahun = DB::table('m_project')
    ->select('tahun', DB::raw('count(nomor_spk) as total'))
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

    // DATA CHART (Stacked Column)
    $chartRaw = DB::table('m_project')
        ->select('tahun', 'customer', DB::raw('count(nomor_spk) as total'))
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
        'years',
        'datasets',
        'maintenanceByCustomer',
        'totalPerTahun',
        'maintenanceEndingSoon'
    ));
    }

}
