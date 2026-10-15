<?php

namespace App\Exports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class PurchasesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $ids;

    public function __construct($ids = null)
    {
        $this->ids = $ids;
    }

    public function collection()
    {
        $query = Purchase::with(['supplier', 'branch', 'user']);

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
            'Reference No',
            'Purchase Date',
            'Supplier Name',
            'Branch Name',
            'Total Amount',
            'Created By',
        ];
    }

    public function map($purchase): array
    {
        return [
            $purchase->id,
            $purchase->reference_no ?? '',
            $purchase->date ? Carbon::parse($purchase->date)->format('Y-m-d H:i:s') : '',
            $purchase->supplier->name ?? 'N/A',
            $purchase->branch->name ?? 'N/A',
            // total_amount is the accessor on the Purchase model -> grand_total
            $purchase->total_amount,
            $purchase->user->name ?? 'N/A',
        ];
    }
}