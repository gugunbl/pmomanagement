<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Mprojectmanager;
use App\Entertainment;
use App\EntertainmentType;



class EntertainmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
public function create()
    {
        return view('entertainment.create', [
            'projects'   => Project::all(),
            'karyawans'  => Mprojectmanager::where('status', 1)->get(), // hanya aktif
            'types'      => EntertainmentType::all(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'karyawan_id' => 'required',
            'project_id' => 'required',
            'entertainment_type_id' => 'required',
            'jumlah' => 'required|numeric'
        ]);

        $data = $request->all();

        if ($request->hasFile('nota')) {
            $data['nota'] = $request->file('nota')->store('nota','public');
        }

        $data['is_transferred'] = $request->has('is_transferred');
        $data['is_reimbursed'] = $request->has('is_reimbursed');

        Entertainment::create($data);

        return redirect()->route('entertainment.index')
            ->with('success','Data berhasil ditambahkan');
    }

}
