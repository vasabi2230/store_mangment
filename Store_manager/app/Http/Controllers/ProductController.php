<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $search  = trim($request->input('search'));
        $perPage = $request->input('per_page', 25);

        $products = Product::with(['category', 'unit'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('code', 'LIKE', "%{$search}%")
                      ->orWhereHas('category', function ($catQuery) use ($search) {
                          $catQuery->where('name', 'LIKE', "%{$search}%");
                      })
                      ->orWhereHas('unit', function ($unitQuery) use ($search) {
                          $unitQuery->where('name', 'LIKE', "%{$search}%");
                      });

                    if (strtolower($search) === 'n/a' || strtolower($search) === 'na') {
                        $q->orWhereNull('category_id')
                          ->orWhereNull('unit_id');
                    }
                });
            })
            ->orderBy('category_id', 'asc')
            ->paginate($perPage === 'all' ? Product::count() : (int) $perPage)
            ->withQueryString();

        $stats = [
            'total_products' => Product::count(),
            'total_spend'    => Product::sum('price'),
            'total_qty'      => Product::sum('quantity'),
        ];

        return view('products.index', compact('products', 'search', 'perPage', 'stats'));
    }

    public function create()
    {
        $categories = Category::all();
        $units      = Unit::all();
        return view('products.create', compact('categories', 'units'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:255|unique:tbl_products,code',
            'category_id' => 'nullable|exists:tbl_categories,id',
            'unit_id'     => 'nullable|exists:tbl_units,id',
            'cost'        => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/products'), $filename);
            $imagePath = 'uploads/products/' . $filename;
        }

        Product::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'cost'        => $request->filled('cost') ? $request->cost : 0,
            'price'       => $request->filled('price') ? $request->price : 0,
            'quantity'    => $request->filled('quantity') ? $request->quantity : 0,
            'description' => $request->filled('description') ? $request->description : 'N/A',
            'image'       => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Product created successfully!');
    }

    public function show($id)
    {
        $product = Product::with(['category', 'unit'])->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit($id)
    {
        $product    = Product::findOrFail($id);
        $categories = Category::all();
        $units      = Unit::all();
        return view('products.edit', compact('product', 'categories', 'units'));
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => ['required', 'string', 'max:255', Rule::unique('tbl_products', 'code')->ignore($product->id)],
            'category_id' => 'nullable|exists:tbl_categories,id',
            'unit_id'     => 'nullable|exists:tbl_units,id',
            'cost'        => 'required|numeric|min:0',
            'price'       => 'required|numeric|min:0',
            'quantity'    => 'required|integer|min:0',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path($product->image))) {
                @unlink(public_path($product->image));
            }

            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/products'), $filename);
            $validated['image'] = 'uploads/products/' . $filename;
        }

        if (isset($product->user_id)) {
            $validated['user_id'] = $product->user_id;
        }

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->image && file_exists(public_path($product->image))) {
            @unlink(public_path($product->image));
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_products', []);
        if (!empty($ids)) {
            Product::whereIn('id', $ids)->delete();
            return redirect()->back()->with('success', 'Selected products deleted successfully!');
        }
        return redirect()->back()->with('error', 'No products selected.');
    }

    public function export(Request $request)
    {
        $selectedIds = explode(',', $request->query('ids', ''));
        $selectedIds = array_filter($selectedIds);
        
        return Excel::download(new ProductsExport($selectedIds), 'products.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        Excel::import(new ProductsImport, $request->file('excel_file'));

        return redirect()->back()->with('success', 'Products imported successfully!');
    }
}