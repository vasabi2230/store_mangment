<?php

namespace App\Exports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarehousesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        $query = Warehouse::with('branch');

        if ($this->ids) {
            $idArray = is_array($this->ids) ? $this->ids : explode(',', $this->ids);
            $query->whereIn('id', $idArray);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Warehouse Name',
            'Branch',
        ];
    }

    public function map($warehouse): array
    {
        return [
            $warehouse->id,
            $warehouse->name,
            $warehouse->branch->name ?? 'N/A',
        ];
    }
}