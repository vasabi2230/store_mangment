<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\Branch;
use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class WarehouseController extends Controller
{
    /**
     * List all warehouses.
     */
    public function index(Request $request)
    {
        $query = Warehouse::with('branch');

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhereHas('branch', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        $query->orderBy('id', 'desc');

        $perPage = $request->get('per_page', 10);
        if ($perPage === 'all') {
            $warehouses = $query->paginate(max($query->count(), 1));
        } else {
            $warehouses = $query->paginate((int) $perPage);
        }

        $stats = [
            'total_warehouses' => Warehouse::count(),
            'branches_covered' => Warehouse::distinct('branch_id')->count('branch_id'),
        ];

        return view('warehouses.index', compact('warehouses', 'stats'));
    }

    /**
     * Show the create warehouse form.
     */
    public function create()
    {
        $branches = Branch::all();

        return view('warehouses.create', compact('branches'));
    }

    /**
     * Save a new warehouse.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => ['required', Rule::exists('tbl_branches', 'id')],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        try {
            Warehouse::create([
                'branch_id' => $validated['branch_id'],
                'name'      => $validated['name'],
            ]);

            return redirect()->route('warehouses.index')->with('success', 'Warehouse created successfully!');
        } catch (\Throwable $e) {
            Log::error('Warehouse store failed: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Error saving warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Show a single warehouse.
     */
    public function show($id)
    {
        $warehouse = Warehouse::with('branch')->findOrFail($id);

        return view('warehouses.show', compact('warehouse'));
    }

    /**
     * Show the edit form for a warehouse.
     */
    public function edit($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $branches  = Branch::all();

        return view('warehouses.edit', compact('warehouse', 'branches'));
    }

    /**
     * Update an existing warehouse.
     */
    public function update(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $validated = $request->validate([
            'branch_id' => ['required', Rule::exists('tbl_branches', 'id')],
            'name'      => ['required', 'string', 'max:255'],
        ]);

        try {
            $warehouse->update($validated);

            return redirect()->route('warehouses.index')->with('success', 'Warehouse updated successfully!');
        } catch (\Throwable $e) {
            Log::error('Warehouse update failed: ' . $e->getMessage());

            return redirect()->back()->withInput()
                ->with('error', 'Error updating warehouse: ' . $e->getMessage());
        }
    }

    /**
     * Delete a single warehouse.
     * Blocked if purchases still reference it, so you don't orphan purchase records.
     */
    public function destroy($id)
    {
        $warehouse = Warehouse::withCount('purchases')->findOrFail($id);

        if ($warehouse->purchases_count > 0) {
            return redirect()->route('warehouses.index')
                ->with('error', 'Cannot delete "' . $warehouse->name . '" - it is used by ' . $warehouse->purchases_count . ' purchase(s).');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted successfully!');
    }

    /**
     * Delete multiple warehouses at once. Skips any still linked to purchases.
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return redirect()->route('warehouses.index')->with('error', 'No warehouses selected.');
        }

        $warehouses = Warehouse::withCount('purchases')->whereIn('id', $ids)->get();

        $blocked = $warehouses->filter(fn ($w) => $w->purchases_count > 0);
        $deletable = $warehouses->filter(fn ($w) => $w->purchases_count === 0);

        foreach ($deletable as $warehouse) {
            $warehouse->delete();
        }

        if ($blocked->isNotEmpty()) {
            $names = $blocked->pluck('name')->join(', ');
            return redirect()->route('warehouses.index')
                ->with('error', $deletable->count() . ' deleted. Skipped (in use): ' . $names);
        }

        return redirect()->route('warehouses.index')->with('success', $deletable->count() . ' warehouse(s) deleted successfully!');
    }

    /**
     * Export warehouses to an Excel (.xlsx) file.
     */
    public function export(Request $request)
    {
        $ids      = $request->get('ids');
        $fileName = 'warehouses_export_' . date('Y-m-d_H-i-s') . '.xlsx';

        return Excel::download(new WarehousesExport($ids), $fileName);
    }

    /**
     * Import warehouses from a CSV/XLSX file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:csv,txt,xlsx,xls|max:2048'
        ]);

        try {
            Excel::import(new WarehousesImport, $request->file('excel_file'));

            return redirect()->route('warehouses.index')->with('success', 'Warehouses imported successfully!');
        } catch (\Throwable $e) {
            Log::error('Warehouse import failed: ' . $e->getMessage());

            return redirect()->route('warehouses.index')->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}