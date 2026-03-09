<?php

namespace App\Http\Controllers;
use App\MCustomer;
use Illuminate\Http\Request;

class MCustomerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = MCustomer::query();
        if ($request->has('search')) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        $customers = $query->paginate(15);
        $title = 'Manage Customers';
        return view('customer.index', compact('customers', 'title'));
    }

    public function create()
    {
        $title = 'Create Customer';
        return view('customer.create', compact('title'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:m_customers,name']);
        MCustomer::create($request->all());
        flash('Customer created successfully!')->success();
        return redirect()->route('customer.index');
    }

    public function edit($id)
    {
        $customer = MCustomer::findOrFail($id);
        $title = 'Edit Customer';
        return view('customer.edit', compact('customer', 'title'));
    }

    public function update(Request $request, $id)
    {
        $customer = MCustomer::findOrFail($id);
        $request->validate(['name' => 'required|unique:m_customers,name,'.$customer->id]);
        $customer->update($request->all());
        flash('Customer updated successfully!')->success();
        return redirect()->route('customer.index');
    }

    public function destroy($id)
    {
        $customer = MCustomer::findOrFail($id);
        $customer->delete();
        flash('Customer deleted successfully!')->info();
        return back();
    }
}
