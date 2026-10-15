<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $name = $row['name'] ?? null ;
        $code = $row['code'] ?? null ;
        
          // 1. Skip empty rows missing name or code
        if (empty($name) || empty($code)) {
            return null;
        }
        
       // Skip if EITHER the name OR the code already exists
        if (Product::where('code', $code)->orWhere('name', $name)->exists()) {
            return null; 
        }
       return new Product([
            'code'        => $code,
            'name'        => $name,
            'cost'        => $row['cost_price'] ?? 0,
            'price'       => $row['selling_price'] ?? 0,
            'quantity'    => $row['quantity'] ?? 0,
            'description' => $row['description'] ?? null,
        ]);
    }
}