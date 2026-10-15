<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
{
    $search = trim($request->input('search'));

    $sales = Sale::with(['customer'])
        ->when($search, function ($query) use ($search) {
            $query->where('reference_no', 'LIKE', "%{$search}%")
                  ->orWhere('payment_status', 'LIKE', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'LIKE', "%{$search}%");
                  });
        })
        ->orderBy('id', 'desc')
        ->paginate(25);

    $stats = [
        'total_sales' => Sale::count(),
        'grand_total' => Sale::sum('grand_total'),
    ];

    return view('sales.index', compact('sales', 'stats', 'search'));
}
public function create()
{
    $customers = \App\Models\Customer::all();
    $products  = \App\Models\Product::all();
    
    // Include branches/warehouses only if models exist
    $branches = class_exists('\App\Models\Branch') ? \App\Models\Branch::all() : collect();

    return view('sales.create', compact('customers', 'products', 'branches'));
}
public function store(Request $request)
{
    $request->validate([
        'branch_id'   => 'required|exists:tbl_branches,id',
        'customer_id' => 'required|exists:tbl_customers,id',
        'date'        => 'required|date',
        'items'       => 'required|array|min:1',
        'items.*.product_id' => 'required|exists:tbl_products,id',
        'items.*.quantity'   => 'required|numeric|min:1',
        'items.*.unit_price' => 'required|numeric|min:0',
    ]);

    DB::beginTransaction();
    try {
        $grandTotal = 0;
        foreach ($request->items as $item) {
            $grandTotal += ($item['quantity'] * $item['unit_price']);
        }

        $lastSale = Sale::latest('id')->first();
        $nextNumber = $lastSale ? ((int) str_replace('POS-', '', $lastSale->reference_no ?? '0')) + 1 : 1;
        $referenceNo = 'POS-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);

        $sale = Sale::create([
            'reference_no'   => $referenceNo,
            'branch_id'      => $request->branch_id,
            'customer_id'    => $request->customer_id,
            'date'           => $request->date,
            'grand_total'    => $grandTotal,
            'payment_status' => 'Paid',
        ]);

        foreach ($request->items as $item) {
            $subtotal = $item['quantity'] * $item['unit_price'];

            SaleItem::create([
                'sale_id'    => $sale->id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'subtotal'   => $subtotal,
            ]);

            $product = Product::find($item['product_id']);
            if ($product) {
                $product->decrement('quantity', $item['quantity']);
            }
        }

        DB::commit();
        return redirect()->route('sales.index')->with('success', 'Sale completed successfully!');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Failed to save sale: ' . $e->getMessage())->withInput();
    }
}

    public function show($id)
    {
        $sale = Sale::with(['customer', 'branch', 'items.product'])->findOrFail($id);
        return view('sales.show', compact('sale'));
    }

    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->items()->delete();
        $sale->delete();

        return redirect()->route('sales.index')->with('success', 'Sale record deleted successfully!');
    }
}