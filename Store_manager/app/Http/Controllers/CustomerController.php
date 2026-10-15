<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Exports\CustomersExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CustomersImport;
class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page' , 10);
        $search = $request->get('search');
        $query = Customer::query()->orderBy('name' , 'asc');

        if($search){
            $query->where('name','like',"%{$search}%")
                ->orWhere('phone','like' , "%{$search}%")
                ->orWhere('address','like', "%{$search}%");
             }
              
        $customers = ($perPage ==='all')
            ? $query->get()
            :$query->paginate((int) $perPage)->withQueryString();

        $stats = [
            'total_customers' => Customer::count(),
        ];
        return view('customers.index', compact('customers', 'stats'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:300',
        ]);

        Customer::create($request->only(['name', 'phone', 'email','address']));

        return redirect()->route('customers.index')->with('success', 'Customer added successfully!');
    }
    public function edit(Customer $customer){
        return view('customers.edit', compact('customer'));
    }
    public function update(Request $request , Customer $customer){
        $name = $request->filled('name') ? $request->name : $customer->name;
        $phone = $request->filled('phone') ? $request->phone : $customer->phone;
        $email = $request->filled('email') ? $request->email : $customer->email;
        $address = $request->filled('address') ? $request->address : $customer->address;

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'nullable|email|max:250',
            'address' => 'required|string|max:300',
        ]);
        $customer->update([
            'name' =>$name,
            'phone' =>$phone,
            'email' =>$email,
            'address' =>$address,
        ]);
        return redirect()->route('customers.index')->with('success', 'Customer Update Successfully!');
    }
    public function destroy(Customer $customer){
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully');
    }
    public function bulkDelete(Request $request)
{
    $ids = $request->input('selected_customer', []);

    if (!empty($ids)) {
        Customer::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Selected customers deleted successfully!');
    }
    return redirect()->back()->with('error', 'No customers selected.');
}
    public function export(Request $request){
        $ids = $request->query('ids');
        $selectedId = !empty($ids) ? explode(',', $ids): [];
        return Excel::download(new CustomersExport($selectedId),'customers.xlsx');
    }
    public function import(Request $request){
        $request->validate([
            'excel_file'=>'required|mimes:xlsx,xsl,csv'
        ]);
        Excel::import(new CustomersImport,$request->file('excel_file'));
      
        return redirect()->back()->with('success', 'Customers imported successfully!');
    }
}