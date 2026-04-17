<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Perangkat;
use App\Project;
use App\PergantianPerangkat;
use App\StockPerangkat;
use App\MCase;
use Auth;
use Barryvdh\DomPDF\Facade\PDF;

class PergantianPerangkatController extends Controller
{


public function index(Request $request)
{
    $query = PergantianPerangkat::with([
        'perangkatLama.project',
        'perangkatBaru'
    ]);

    // Filter by project
    if ($request->project_id) {
        $query->whereHas('perangkatLama.project', function ($q) use ($request) {
            $q->where('id', $request->project_id);
        });
    }

    // Search
    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('alasan_penggantian', 'like', '%'.$request->search.'%')
              ->orWhereHas('perangkatLama', function ($qq) use ($request) {
                  $qq->where('serialnumber', 'like', '%'.$request->search.'%');
              });
        });
    }

    $pergantians = $query->latest()->paginate(10);

    $projects = Project::pluck('nama_project', 'id_project');


    return view('pergantian.index', compact(
        'pergantians',
        'projects'
    ));
}


public function pdf($id)
{
    $data = PergantianPerangkat::with([
        'perangkatLama.project',
        'perangkatBaru'
    ])->findOrFail($id);

    $pdf = \PDF::loadView('pergantian.pdf', compact('data'));

    return $pdf->stream(
        'BA_Pergantian_'.$data->id_pergantian.'.pdf'
    );
}


    public function create(Request $request, $id_perangkat)
    {
        $perangkat = Perangkat::findOrFail($id_perangkat);

        $stockPerangkat = Perangkat::where('status', 3) // stock/golden unit
            ->where('id_project', $perangkat->id_project)
            ->get();

        $case = null;
        if ($request->id_case) {
            $case = MCase::find($request->id_case);
        }

        return view('pergantian.create', compact('perangkat', 'stockPerangkat', 'case'));
    }

public function store(Request $request)
{
    $request->validate([
        'id_perangkat_lama' => 'required|exists:m_perangkat,id_perangkat',
        'jenis_penggantian' => 'required|in:stock,rma_principal',

        'id_perangkat_baru' => [
            'nullable',
            function ($attr, $value, $fail) use ($request) {
                if ($request->jenis_penggantian === 'stock' && empty($value)) {
                    $fail('Perangkat pengganti wajib dipilih jika dari stock.');
                }
            }
        ],

        'user_pelapor_id' => 'nullable',
        'user_pelapor_nama' => 'nullable',
        'user_pelapor_jabatan' => 'nullable',

        'user_approval_id' => 'nullable',
        'user_approval_nama' => 'nullable',
        'user_approval_jabatan' => 'nullable',

        'tanggal_pergantian' => 'required|date',
    ]);

    DB::transaction(function () use ($request) {

        // 🔒 Ambil perangkat lama
        $perangkatLama = Perangkat::lockForUpdate()
            ->findOrFail($request->id_perangkat_lama);

        // ==========================
        // SIMPAN DATA PERGANTIAN
        // ==========================
        PergantianPerangkat::create([
            'id_perangkat_lama' => $perangkatLama->id_perangkat,
            'id_perangkat_baru' => $request->id_perangkat_baru,
            'jenis_penggantian' => $request->jenis_penggantian,
            'alasan_penggantian'=> $request->alasan_penggantian,
            'tanggal_pergantian'=> $request->tanggal_pergantian,

            'user_pelapor_id' => $request->user_pelapor_id,
            'user_pelapor_nama' => $request->user_pelapor_nama,
            'user_pelapor_jabatan' => $request->user_pelapor_jabatan,

            'user_approval_id' => $request->user_approval_id,
            'user_approval_nama' => $request->user_approval_nama,
            'user_approval_jabatan' => $request->user_approval_jabatan,

            'status' => $request->jenis_penggantian === 'rma_principal'
                        ? 'waiting_rma'
                        : 'approved',
        ]);

        // ==========================
        // JIKA DARI STOCK
        // ==========================
        if ($request->jenis_penggantian === 'stock') {

            // 🔒 Ambil perangkat baru
            $perangkatBaru = Perangkat::lockForUpdate()
                ->findOrFail($request->id_perangkat_baru);

            // Simpan lokasi lama
            $siteLama   = $perangkatLama->site;
            $lantaiLama = $perangkatLama->lantai;

            $siteStock   = $perangkatBaru->site;
            $lantaiStock = $perangkatBaru->lantai;

            // ==========================
            // BUAT TEKS KETERANGAN
            // ==========================
            $tgl = $request->tanggal_pergantian;
            $snLama = $perangkatLama->serialnumber;
            $snBaru = $perangkatBaru->serialnumber;

            $logKeterangan = "[{$tgl}] Pergantian perangkat dari SN {$snLama} ke SN {$snBaru}";

            // 🔁 Update perangkat baru → AKTIF + keterangan
            $perangkatBaru->update([
                'status' => 1,
                'site'   => $siteLama,
                'lantai' => $lantaiLama,
                'keterangan' => trim(
                    ($perangkatBaru->keterangan ? $perangkatBaru->keterangan . "\n" : '')
                    . $logKeterangan
                ),
            ]);

            // 🔁 Update perangkat lama → STOCK + append keterangan lama
            $perangkatLama->update([
                'status' => 3,
                'site'   => $siteStock,
                'lantai' => $lantaiStock,
                'keterangan' => trim(
                    ($perangkatLama->keterangan ? $perangkatLama->keterangan . "\n" : '')
                    . $logKeterangan
                ),
            ]);
        }

        // ==========================
        // JIKA RMA PRINCIPAL
        // ==========================
        if ($request->jenis_penggantian === 'rma_principal') {

            $tgl = $request->tanggal_pergantian;
            $snLama = $perangkatLama->serialnumber;

            $logRma = "[{$tgl}] Perangkat dengan SN {$snLama} masuk proses RMA Principal";

            $perangkatLama->update([
                'status' => 2,
                'keterangan' => trim(
                    ($perangkatLama->keterangan ? $perangkatLama->keterangan . "\n" : '')
                    . $logRma
                ),
            ]);
        }
    });

    
    return redirect()
    ->route('pergantian.index')
    ->with('success', 'Pergantian perangkat berhasil disimpan');

}



    public function approve($id)
    {
        $pergantian = PergantianPerangkat::findOrFail($id);

        $pergantian->update([
            'user_approval_id' => Auth::id(),
            'user_approval_nama' => Auth::user()->name,
            'user_approval_jabatan' => Auth::user()->jabatan,
            'status' => 'done'
        ]);

        return back()->with('success', 'Pergantian perangkat disetujui');
    }
}
