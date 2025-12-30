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
    foreach ($customers as $customer) {
        $datasets[] = [
            'label' => $customer,
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
