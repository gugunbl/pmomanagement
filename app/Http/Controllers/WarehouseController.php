<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\Warehouse;
use App\WarehouseBarang;
use App\WarehousePerangkat;
use Barryvdh\DomPDF\Facade\Pdf;


class WarehouseController extends Controller
{

        public function perangkatWarehouse()
        {
            $perangkats = WarehousePerangkat::orderBy('tgl_dokumen', 'desc')
                            ->paginate(15);

            return view('warehouse.perangkat', compact('perangkats'));
        }


        public function update(Request $request, $no_dokumen)
        {
            $request->validate([
                'tgl_dokumen' => 'required|date',
                'nama_pemohon' => 'required',
                'nama_perusahaan' => 'required',
                'departemen' => 'required',
                'jabatan' => 'required',
                'barang.*.nama_barang' => 'required',
                'barang.*.serialnumber' => 'required',
                'barang.*.quantity' => 'required|numeric|min:1'
            ]);

            DB::beginTransaction();

            try {

                // UPDATE HEADER
                Warehouse::where('no_dokumen', $no_dokumen)->update([
                    'tgl_dokumen' => $request->tgl_dokumen,
                    'nama_pemohon' => $request->nama_pemohon,
                    'nama_perusahaan' => $request->nama_perusahaan,
                    'departemen' => $request->departemen,
                    'jabatan' => $request->jabatan,
                    'lokasi_tujuan_asal' => $request->lokasi_tujuan_asal,
                    'tgl_masuk_keluar' => $request->tgl_masuk_keluar,
                    'jam_masuk_keluar' => $request->jam_masuk_keluar,
                    'catatan' => $request->catatan,
                ]);

                // HAPUS DETAIL LAMA
                WarehouseBarang::where('no_dokumen', $no_dokumen)->delete();

                // SIMPAN DETAIL BARU
                foreach ($request->barang as $item) {
                    WarehouseBarang::create([
                        'no_dokumen' => $no_dokumen,
                        'id_project' => $request->id_project,
                        'serialnumber' => $item['serialnumber'],
                        'nama_barang' => $item['nama_barang'],
                        'quantity' => $item['quantity'],
                        'deskripsi' => $item['deskripsi'] ?? null,
                        'user_id' => auth()->id()
                    ]);
                }

                DB::commit();

                return redirect()
                    ->route('warehouse.show', $no_dokumen)
                    ->with('success', 'Data warehouse berhasil diperbarui');

            } catch (\Exception $e) {
                DB::rollback();
                return back()->with('error', $e->getMessage());
            }
        }



    
        public function index(Request $request)
        {
            $jenis = $request->jenis_dokumen; // filter dropdown

            $warehouses = DB::table('t_warehouse as w')
                ->join('t_warehouse_barang as wb', 'w.no_dokumen', '=', 'wb.no_dokumen')
                ->select(
                    'w.no_dokumen',
                    'w.tgl_dokumen',
                    'w.jenis_dokumen',
                    'w.nama_pemohon',
                    DB::raw('COUNT(wb.serialnumber) as jmlbarang')
                )
                ->when($jenis, function ($q) use ($jenis) {
                    $q->where('w.jenis_dokumen', $jenis);
                })
                ->groupBy(
                    'w.no_dokumen',
                    'w.tgl_dokumen',
                    'w.jenis_dokumen',
                    'w.nama_pemohon'
                )
                ->orderBy('w.tgl_dokumen', 'desc')
                ->paginate(10);

            return view('warehouse.index', compact('warehouses', 'jenis'));
        }


        public function show($no_dokumen)
        {
            $warehouse = Warehouse::where('no_dokumen', $no_dokumen)->firstOrFail();
            $barangs   = WarehouseBarang::where('no_dokumen', $no_dokumen)->get();

            return view('warehouse.show', compact('warehouse', 'barangs'));
        }



        public function exportPdf($no_dokumen)
        {
            $warehouse = Warehouse::where('no_dokumen', $no_dokumen)->firstOrFail();
            $barangs   = WarehouseBarang::where('no_dokumen', $no_dokumen)->get();

            $pdf = Pdf::loadView('warehouse.pdf', compact('warehouse', 'barangs'))
                    ->setPaper('A4', 'portrait');

            return $pdf->download('Warehouse-'.$no_dokumen.'.pdf');
        }

        public function edit($no_dokumen)
        {
            $warehouse = Warehouse::where('no_dokumen', $no_dokumen)->firstOrFail();
            $barangs   = WarehouseBarang::where('no_dokumen', $no_dokumen)->get();

            $documents = [
                1 => 'Barang Masuk',
                2 => 'Barang Keluar'
            ];

            return view('warehouse.edit', compact(
                'warehouse',
                'barangs',
                'documents'
            ));
        }

        public function create()
    {

        $documents= [
            '1'=>'Barang Masuk',
            '2'=>'Barang Keluar'
        ];
        $perusahaans=[
            '11'=>'PT Info Solusindo Data Utama',
            '22'=>'PT SMAS',
            '33'=>'PT PSN'
        ];
        $podos=[
            '1'=>'PO - Purchase Order',
            '2'=>'DO - Delivery Order'
        ];
        $projectes = Project::pluck('nama_project', 'id_project');
        $title = 'Masuk / Keluar Gudang';
        return view('warehouse.create', compact('title','documents','projectes','perusahaans','podos'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'no_dokumen' => 'required',
            'id_project' => 'required',
            'jenis_dokumen' => 'required',
            'tgl_dokumen' => 'required|date',
            'nama_pemohon' => 'required',
            'nama_perusahaan' => 'required',
            'departemen' => 'required',
            'jabatan' => 'required',
            'barang.*.nama_barang' => 'required',
            'barang.*.serialnumber' => 'required',
            'barang.*.quantity' => 'required|numeric|min:1',
            'po_do' => 'nullable|integer',
            'user_id' => 'nullable'
        ]);
        $noDokumen = $this->generateNoDokumen();

        DB::beginTransaction();

        try {

            // =====================
            // SIMPAN HEADER
            // =====================
            $warehouse = Warehouse::create([
                //'no_dokumen' => $request->no_dokumen,
                'no_dokumen' => $noDokumen,
                'id_project' => $request->id_project,
                'jenis_dokumen' => $request->jenis_dokumen,
                'tgl_dokumen' => $request->tgl_dokumen,
                'nama_pemohon' => $request->nama_pemohon,
                'nama_perusahaan' => $request->nama_perusahaan,
                'departemen' => $request->departemen,
                'jabatan' => $request->jabatan,
                'po_do' => $request->po_do,
                'nomor_po_do' => $request->nomor_po_do,
                'lokasi_tujuan_asal' => $request->lokasi_tujuan_asal,
                //'tgl_masuk_keluar' => now()->toDateString(),
                //'jam_masuk_keluar' => now()->format('H:i'),
                'tgl_masuk_keluar' => $request->tgl_masuk_keluar,
                'jam_masuk_keluar' => $request->jam_masuk_keluar,
                'catatan' => $request->catatan,
                'user_id' => auth()->id()
            ]);

            // =====================
            // SIMPAN DETAIL BARANG & UPDATE PERANGKAT
            // =====================
            foreach ($request->barang as $item) {
                $wb = WarehouseBarang::create([
                    'no_dokumen' => $noDokumen,
                    'id_project' => $request->id_project,
                    'serialnumber' => $item['serialnumber'],
                    'nama_barang' => $item['nama_barang'],
                    'quantity' => $item['quantity'],
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'user_id' => auth()->id()
                ]);

                // Update Status Perangkat
                $perangkat = \App\Perangkat::where('serialnumber', $item['serialnumber'])->first();
                if ($perangkat) {
                    $updateData = [
                        'id_warehouse_transaksi' => $warehouse->id_transaksi,
                    ];

                    if ($request->jenis_dokumen == 1) { // Barang Masuk
                        $updateData['status_server'] = 'In Warehouse';
                        $updateData['site'] = 'Warehouse - ' . ($request->lokasi_tujuan_asal ?? 'Office');
                    } else if ($request->jenis_dokumen == 2) { // Barang Keluar
                        $updateData['status_server'] = 'Deployed';
                        $updateData['site'] = $request->lokasi_tujuan_asal; // Lokasi tujuan
                    }

                    $perangkat->update($updateData);
                }
            }

            DB::commit();

            return redirect()
                ->route('warehouse.index')
                ->with('success', 'Transaksi warehouse ' . $noDokumen . ' berhasil disimpan dan status perangkat telah diupdate.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }


    private function generateNoDokumen()
{
    $year = date('Y');

    // ambil no terakhir di tahun berjalan
    $last = Warehouse::whereYear('created_at', $year)
        ->orderBy('id_transaksi', 'desc')
        ->first();

    if (!$last) {
        $number = 1;
    } else {
        // ambil 4 digit terakhir
        $lastNumber = (int) substr($last->no_dokumen, -4);
        $number = $lastNumber + 1;
    }

    return 'WH-' . $year . '-' . str_pad($number, 4, '0', STR_PAD_LEFT);
}

}