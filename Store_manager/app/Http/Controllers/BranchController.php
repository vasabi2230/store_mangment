<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Exports\BranchesExport;
use Maatwebsite\Excel\Facades\Excel;
class BranchController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $search = $request->get('search');

        $query = Branch::query()->orderBy('name', 'asc');

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('address', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
        }

        $branches = ($perPage === 'all') 
            ? $query->get() 
            : $query->paginate((int) $perPage)->withQueryString();

          $stats = [
            'total_branches' => Branch::count(),
        ];
        return view('branches.index', compact('branches', 'stats', 'perPage', 'search'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:200',
            'phone'   => 'required|numeric|min:0',
            'address' => 'required|string|max:500',
            'email'   => 'nullable|string|max:200',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/branches'), $filename);
            $logoPath = 'uploads/branches/' . $filename;
        }

        Branch::create([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'email'   => $request->email ?? 'N/A',
            'logo'    => $logoPath,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch created successfully!');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        

        $branch = Branch::findOrFail($id);

        $request->validate([
            'name'    => 'required|string|max:200',
            'phone'   => 'required|numeric|min:0',
            'address' => 'required|string|max:500',
            'logo'    => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $logoPath = $branch->logo;

        if ($request->hasFile('logo')) {
            if ($branch->logo && file_exists(public_path($branch->logo))) {
                @unlink(public_path($branch->logo));
            }

            $file = $request->file('logo');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/branches'), $filename);
            $logoPath = 'uploads/branches/' . $filename;
        }

        $branch->update([
            'name'    => $request->name,
            'phone'   => $request->phone,
            'address' => $request->address,
            'email'   =>$request->filled('email') ? $request->email : null,
            'logo'    => $logoPath,
        ]);

        return redirect()->route('branches.index')->with('success', 'Branch updated successfully!');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        if ($branch->logo && file_exists(public_path($branch->logo))) {
            @unlink(public_path($branch->logo));
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Branch deleted successfully!');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('selected_branches', []);

        if (!empty($ids)) {
            Branch::whereIn('id', $ids)->delete();
            return redirect()->route('branches.index')->with('success', 'Selected branches deleted successfully!');
        }

        return redirect()->route('branches.index')->with('error', 'No branches selected.');
    }
    public function export(Request $request){
        $ids = $request->query('ids');
        $selectedId = !empty($ids) ? explode(',' , $ids) : [] ; 
        return Excel::download(new BranchesExport($selectedId), 'branches.xlsx');
    }
}