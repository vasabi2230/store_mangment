<?php

namespace App\Imports;

use App\Models\Warehouse;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class WarehousesImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    /**
     * Expected heading row: branch_id, name
     */
    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        return new Warehouse([
            'branch_id' => $row['branch_id'] ?: null,
            'name'      => trim($row['name']),
        ]);
    }
}