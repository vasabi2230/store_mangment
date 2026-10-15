<?php

namespace App\Imports;

use App\Models\Purchase;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchasesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Expected heading row:
     * date, supplier_id, branch_id, warehouse_id, total_amount
     *
     * NOTE: tbl_purchases has NO total_amount column. The spreadsheet
     * heading stays "total_amount" for the user's convenience, but the
     * value is written into the real columns: total + grand_total.
     */
    public function model(array $row)
    {
        if (empty($row['date']) && empty($row['total_amount'])) {
            return null;
        }

        $amount = is_numeric($row['total_amount'] ?? null)
            ? (float) $row['total_amount']
            : 0;

        return new Purchase([
            'date'           => $this->parseDate($row['date'] ?? null),
            'supplier_id'    => $row['supplier_id'] ?: null,
            'branch_id'      => $row['branch_id'] ?: null,
            'warehouse_id'   => $row['warehouse_id'] ?: null,
            'total'          => $amount,
            'grand_total'    => $amount,
            'paid'           => 0,
            'balance'        => $amount,
            'status'         => 'completed',
            'payment_status' => 'pending',
            'user_id'        => Auth::id() ?? 1,
        ]);
    }

    /**
     * Excel stores dates as serial numbers (e.g. 45920), so a plain
     * Carbon::parse() would fail or give a wrong date.
     */
    private function parseDate($value): string
    {
        if (empty($value)) {
            return now()->format('Y-m-d H:i:s');
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))
                    ->format('Y-m-d H:i:s');
            }

            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return now()->format('Y-m-d H:i:s');
        }
    }
}