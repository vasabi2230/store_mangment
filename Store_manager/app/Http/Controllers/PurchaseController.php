<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Product;
use App\Exports\PurchasesExport;
use App\Imports\PurchasesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    /**
     * List all purchases.
     */
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'branch', 'warehouse', 'user', 'items.product']);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        $query->orderBy('id', 'desc');

        $perPage = $request->get('per_page', 10);
        if ($perPage === 'all') {
            $purchases = $query->paginate(max($query->count(), 1));
        } else {
            $purchases = $query->paginate((int) $perPage);
        }

        // Summary cards - computed independently of the filtered/paginated list above.
        $stats = [
            'total_purchases' => Purchase::count(),
            'total_spend'     => Purchase::sum('grand_total'),
            'this_month'      => Purchase::whereMonth('date', now()->month)
                                    ->whereYear('date', now()->year)
                                    ->sum('grand_total'),
            'pending_payment' => Purchase::where('payment_status', 'pending')->count(),
        ];

        return view('purchases.index', compact('purchases', 'stats'));
    }

    /**
     * Show the create purchase form.
     */
    public function create()
    {
        $suppliers = Supplier::all();
        $branches  = Branch::all();
        $products  = Product::all();

        return view('purchases.create', compact('suppliers', 'branches', 'products'));
    }

    /**
     * Save a new purchase + its items.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id'           => ['required', Rule::exists('tbl_branches', 'id')],
            'supplier_id'         => ['required', Rule::exists('tbl_suppliers', 'id')],
            'date'                => ['required', 'date'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', Rule::exists('tbl_products', 'id')],
            'items.*.cost'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'numeric', 'min:1'],
        ], [
            'items.required' => 'Please add at least one product to the table before saving.',
        ]);

        DB::beginTransaction();

        try {
            $formattedDate = Carbon::parse($request->date)->format('Y-m-d H:i:s');

            // tbl_purchases has a reference_no column - generate one.
            $nextId      = (Purchase::max('id') ?? 0) + 1;
            $referenceNo = 'PUR-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);

            $purchase = Purchase::create([
                'branch_id'      => $request->branch_id,
                'supplier_id'    => $request->supplier_id,
                'warehouse_id'   => $request->warehouse_id ?: null,
                'user_id'        => Auth::id() ?? 1,
                'date'           => $formattedDate,
                'reference_no'   => $referenceNo,
                'total'          => 0,
                'grand_total'    => 0,
                'shipping'       => 0,
                'paid'           => 0,
                'balance'        => 0,
                'status'         => 'completed',
                'payment_status' => 'pending',
            ]);

            $grandTotal = 0;

            foreach ($validated['items'] as $pi) {
                $productId = $pi['product_id'];
                $cost      = (float) $pi['cost'];
                $quantity  = (float) $pi['quantity'];
                $subtotal  = $cost * $quantity;

                $grandTotal += $subtotal;

                $product = Product::find($productId);

                PurchaseItem::create([
                    'purchase_id'   => $purchase->id,
                    'product_id'    => $productId,
                    'unit_id'       => $product ? $product->unit_id : null,
                    'unit_quantity' => $quantity,
                    'unit_cost'     => $cost,
                    'quantity'      => $quantity,
                    'cost'          => $cost,
                    'subtotal'      => $subtotal,
                ]);

                if ($product) {
                    $product->increment('quantity', $quantity);
                }
            }

            $purchase->total       = $grandTotal;
            $purchase->grand_total = $grandTotal;
            $purchase->balance     = $grandTotal;
            $purchase->save();

            DB::commit();

            return redirect()->route('purchases.index')
                ->with('success', 'Purchase ' . $referenceNo . ' created successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Purchase store failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Error saving purchase: ' . $e->getMessage());
        }
    }

    /**
     * Show a single purchase.
     */
    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'branch', 'user', 'items.product'])
            ->findOrFail($id);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the edit form for a purchase.
     */
    public function edit($id)
    {
        $purchase  = Purchase::with('items')->findOrFail($id);
        $suppliers = Supplier::all();
        $branches  = Branch::all();
        $products  = Product::all();

        return view('purchases.edit', compact('purchase', 'suppliers', 'branches', 'products'));
    }

    /**
     * Update an existing purchase.
     * Reverses old stock quantities, replaces the items, re-applies new stock.
     */
    public function update(Request $request, $id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        $validated = $request->validate([
            'branch_id'           => ['required', Rule::exists('tbl_branches', 'id')],
            'supplier_id'         => ['required', Rule::exists('tbl_suppliers', 'id')],
            'date'                => ['required', 'date'],
            'items'               => ['required', 'array', 'min:1'],
            'items.*.product_id'  => ['required', Rule::exists('tbl_products', 'id')],
            'items.*.cost'        => ['required', 'numeric', 'min:0'],
            'items.*.quantity'    => ['required', 'numeric', 'min:1'],
        ]);

        DB::beginTransaction();

        try {
            // Reverse stock added by the old items before replacing them.
            foreach ($purchase->items as $oldItem) {
                $product = Product::find($oldItem->product_id);
                if ($product) {
                    $product->decrement('quantity', $oldItem->quantity);
                }
            }
            $purchase->items()->delete();

            $formattedDate = Carbon::parse($request->date)->format('Y-m-d H:i:s');

            $purchase->branch_id    = $request->branch_id;
            $purchase->supplier_id  = $request->supplier_id;
            $purchase->warehouse_id = $request->warehouse_id ?: null;
            $purchase->date         = $formattedDate;

            $grandTotal = 0;

            foreach ($validated['items'] as $pi) {
                $productId = $pi['product_id'];
                $cost      = (float) $pi['cost'];
                $quantity  = (float) $pi['quantity'];
                $subtotal  = $cost * $quantity;

                $grandTotal += $subtotal;

                $product = Product::find($productId);

                PurchaseItem::create([
                    'purchase_id'   => $purchase->id,
                    'product_id'    => $productId,
                    'unit_id'       => $product ? $product->unit_id : null,
                    'unit_quantity' => $quantity,
                    'unit_cost'     => $cost,
                    'quantity'      => $quantity,
                    'cost'          => $cost,
                    'subtotal'      => $subtotal,
                ]);

                if ($product) {
                    $product->increment('quantity', $quantity);
                }
            }

            $purchase->total       = $grandTotal;
            $purchase->grand_total = $grandTotal;
            $purchase->balance     = $grandTotal - $purchase->paid;
            $purchase->save();

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Purchase update failed: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->back()->withInput()
                ->with('error', 'Error updating purchase: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single purchase (reverses stock first).
     */
    public function destroy($id)
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        DB::beginTransaction();

        try {
            foreach ($purchase->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->decrement('quantity', $item->quantity);
                }
            }

            $purchase->items()->delete();
            $purchase->delete();

            DB::commit();

            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')->with('error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete multiple purchases at once (reverses stock for each).
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return redirect()->route('purchases.index')->with('error', 'No purchases selected.');
        }

        DB::beginTransaction();

        try {
            $purchases = Purchase::with('items')->whereIn('id', $ids)->get();

            foreach ($purchases as $purchase) {
                foreach ($purchase->items as $item) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->decrement('quantity', $item->quantity);
                    }
                }
                $purchase->items()->delete();
                $purchase->delete();
            }

            DB::commit();

            return redirect()->route('purchases.index')->with('success', count($ids) . ' purchase(s) deleted successfully!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')->with('error', 'Bulk delete failed: ' . $e->getMessage());
        }
    }

    /**
     * Export purchases to an Excel (.xlsx) file, via App\Exports\PurchasesExport.
     */
    public function export(Request $request)
    {
        $ids      = $request->get('ids');
        $fileName = 'purchases_export_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new PurchasesExport($ids), $fileName);
    }

    /**
     * Import purchases from a CSV/XLSX file, via App\Imports\PurchasesImport.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        try {
            Excel::import(new PurchasesImport, $request->file('excel_file'));

            return redirect()->route('purchases.index')->with('success', 'Purchases imported successfully!');
        } catch (\Throwable $e) {
            Log::error('Purchase import failed: ' . $e->getMessage());

            return redirect()->route('purchases.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}