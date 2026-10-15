<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // Added Request $request here
    public function index(Request $request) 
    {
        $search  = trim($request->input('search'));
        $perPage = $request->input('per_page', 25);

        $suppliers = Supplier::when($search, function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%");
            });
        })
        ->orderBy('id', 'DESC')
        ->paginate($perPage === 'all' ? (Supplier::count() ?: 1) : (int) $perPage)
        ->withQueryString();

        $stats = [
            'total_suppliers' => Supplier::count(),
        ];

        return view('suppliers.index', compact('suppliers', 'stats'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'email'   => 'nullable|email|max:255',
            'address' => 'required|string|max:255',
        ]);

        Supplier::create($request->only(['name', 'phone', 'email', 'address']));

        return redirect()->route('suppliers.index')->with('success', 'Supplier added successfully!');
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'phone'   => 'required|string|max:50',
            'email'   => 'nullable|email|max:250',
            'address' => 'required|string|max:300',
        ]);

        $supplier->update([
            'name'    => $request->input('name', $supplier->name),
            'phone'   => $request->input('phone', $supplier->phone),
            'email'   => $request->input('email', $supplier->email),
            'address' => $request->input('address', $supplier->address),
        ]);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully!');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted successfully!');
    }
}