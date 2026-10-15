<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::all();
        return view('units.index', compact('units'));
    }

    public function create()
    {
        return view('units.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        Unit::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit created successfully!');
    }

    public function edit(Unit $unit)
    {
        return view('units.edit', compact('unit'));
    }

    public function update(Request $request, Unit $unit)
    {
        $name = $request->filled('name') ? $request->name : $unit->name;

        $request->merge([
            'name' => $name,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $unit->update([
            'name' => $name,
            'description' => $request->filled('description') ? $request->description : null,
        ]);

        return redirect()->route('units.index')->with('success', 'Unit updated successfully!');
    }
}