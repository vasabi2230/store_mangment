<?php

namespace App\Exports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $selectedIds;

    public function __construct(array $selectedIds = [])
    {
        $this->selectedIds = $selectedIds;
    }

    public function collection()
    {
        if (!empty($this->selectedIds)) {
            return Category::whereIn('id', $this->selectedIds)->get();
        }

        return Category::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Code',
            'Category Name',
            'Description',
        ];
    }

    public function map($category): array
    {
        return [
            $category->id,
            $category->code ?? 'N/A',
            $category->name,
            $category->description ?? 'None',
        ];
    }
}