<?php

namespace App\Http\Controllers;

use App\Models\Category;

use Illuminate\Http\Request;
use App\Exports\CategoriesExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CategoriesImport;
class CategoryController extends Controller
{
    // Display list
   public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $search = $request->get('search');

    $categories = Category::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('code', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
        })
        ->orderBy('id', 'desc') // <--- Replace ->latest() with this
         ->paginate($perPage === 'all' ? Category::count() : (int) $perPage)
        ->withQueryString();

    return view('categories.index', compact('categories'));
}
    // Show form to create new category
    public function create()
    {
        return view('categories.create');
    }

    // Store new category in database
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'code'        => 'required|string|max:50|unique:tbl_categories,code',
            'description' => 'nullable|string|max:255',
        ]);

        Category::create([
            'name'        => $request->name,
            'code'        => $request->code,
            'description' => $request->description ?? '', // Fallback to empty string for NOT NULL DB column
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully!');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    // Update category
    public function update(Request $request, Category $category)
{
    // 1. Fallback to current values if inputs are left blank by the user
    $name = $request->filled('name') ? $request->name : $category->name;
    $code = $request->filled('code') ? $request->code : $category->code;

    // 2. Merge fallback values before validation
    $request->merge([
        'name' => $name,
        'code' => $code,
    ]);

    // 3. Validate - redirect back with errors occurs automatically if duplicate code is found
    $request->validate([
        'code' => 'required|string|max:50|unique:tbl_categories,code,' . $category->id,
        'name' => 'required|string|max:255',
    ], [
        'code.unique' => 'The category code "' . $request->code . '" is already in use by another category.',
    ]);

    // 4. Perform update
    $category->update([
        'name'        => $name,
        'code'        => $code,
        // In store() and update()
'description' => $request->filled('description') ? $request->description : null,
    ]);

    return redirect()->route('categories.index')->with('success', 'Category updated successfully!');
}

    // Delete category
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully!');
    }

   public function export(Request $request)
    {
        $ids = $request->query('ids');
        $selectedIds = !empty($ids) ? explode(',', $ids) : [];

        // Make sure it downloads categories.xlsx using CategoriesExport
        return Excel::download(new CategoriesExport($selectedIds), 'categories.xlsx');
    }
    public function import(Request $request)
{
    $request->validate([
        'excel_file' => 'required|mimes:xlsx,xls,csv'
    ]);

    Excel::import(new CategoriesImport, $request->file('excel_file'));

    return redirect()->back()->with('success', 'Category imported successfully!');
}
public function bulkDelete(Request $request)
{
    $ids = $request->input('ids', []);
    
    if (!empty($ids)) {
        Category::whereIn('id', $ids)->delete();
        return redirect()->back()->with('success', 'Selected categories deleted successfully.');
    }

    return redirect()->back()->with('error', 'No categories selected.');
}
}