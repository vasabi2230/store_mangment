<?php

namespace App\Imports;

use App\Models\Category;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $name = $row['name'] ?? null;
        $code = $row['code'] ?? null;

        // 1. Skip empty rows missing name or code
        if (empty($name) || empty($code)) {
            return null;
        }

        // 2. Skip if the category code already exists in the database
    
        if (Category::where('code', $code)->orWhere('name', $name)->exists()) {
            return null; 
        }
     

        // 3. Otherwise, insert the new category
        return new Category([
            'code'        => $code,
            'name'        => $name,
            'description' => $row['description'] ?? null,
        ]);
    }
}

