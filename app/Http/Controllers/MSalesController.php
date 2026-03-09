<?php

namespace App\Http\Controllers;
use App\MSales;
use Illuminate\Http\Request;

class MSalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = MSales::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        $saless = $query->paginate(15);
        $title = 'Manage Sales';
        return view('sales.index', compact('saless', 'title'));
    }

    public function create()
    {
        $title = 'Create Sales';
        return view('sales.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:m_sales,name']);
        MSales::create($request->all());
        flash('Sales created successfully!')->success();
        return redirect()->route('sales.index');
    }

    public function edit($id)
    {
        $sales = MSales::findOrFail($id);
        $title = 'Edit Sales';
        return view('sales.edit', compact('sales', 'title'));
    }

    public function update(Request $request, $id)
    {
        $sales = MSales::findOrFail($id);
        $request->validate(['name' => 'required|unique:m_sales,name,'.$sales->id]);
        $sales->update($request->all());
        flash('Sales updated successfully!')->success();
        return redirect()->route('sales.index');
    }

    public function destroy($id)
    {
        $sales = MSales::findOrFail($id);
        $sales->delete();
        flash('Sales deleted successfully!')->info();
        return back();
    }
}
