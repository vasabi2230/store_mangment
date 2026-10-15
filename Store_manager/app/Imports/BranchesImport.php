<?php

namespace App\Imports;

use App\Models\Branch;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BranchesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $name = $row['name'] ?? null;
        $phone = $row['phone'] ?? null;
        $address = $row['address'] ?? null;

        if(empty($name) || empty($phone) || empty($address)){
            return null;
        }
       if (Branch::where('phone', $phone)->orWhere('name', $name)->exists()) {
            return null;
        }

        return new Branch([
            'name' => $name,
            'phone' => $phone,
            'email' =>$row['email'] ?? null,
            'address' => $address,
        ]);
    }
}
