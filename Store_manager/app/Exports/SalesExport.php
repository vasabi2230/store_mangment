<?php

namespace App\Exports;

use App\Models\Sale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $search;

    public function __construct($search = null)
    {
        $this->search = $search;
    }

    public function collection()
    {
        return Sale::with(['customer'])
            ->when($this->search, function ($query) {
                $query->where('reference_no', 'LIKE', "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('name', 'LIKE', "%{$this->search}%");
                      });
            })
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Date', 'Reference No', 'Customer ID', 
            'Grand Total', 'Paid', 'Balance', 'Status', 'Payment Status'
        ];
    }

    public function map($sale): array
    {
        return [
            $sale->id,
            $sale->date,
            $sale->reference_no,
            $sale->customer ? $sale->customer->name : $sale->customer_id,
            $sale->grand_total,
            $sale->paid,
            $sale->balance,
            $sale->status,
            $sale->payment_status,
        ];
    }
}