<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $selectedIds;

    public function __construct($selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        if (!empty($this->selectedIds)) {
            return Product::with(['category', 'unit'])->whereIn('id', $this->selectedIds)->get();
        }
        return Product::with(['category', 'unit'])->all();
    }

    public function headings(): array
    {
        return ['Code', 'Name', 'Category', 'Unit', 'Cost Price', 'Selling Price', 'Quantity'];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->name,
            $product->category->name ?? 'N/A',
            $product->unit->name ?? 'N/A',
            $product->cost,
            $product->price,
            $product->quantity,
        ];
    }
}