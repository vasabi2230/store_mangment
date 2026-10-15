<?php

namespace App\Exports;

use App\Models\Branch;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class BranchesExport implements FromCollection, WithHeadings, WithMapping
{

    /**
    * @return \Illuminate\Support\Collection
    */

    protected $selectedIds;
    public function __construct(array $selectedIds =[]){
        $this -> selectedIds = $selectedIds;
    }

    public function collection()
    {
        if(!empty($this->selectedIds)){
            return Branch::whereIn('id', $this->selectedIds)->get();
        }
        return Branch::all();
    }
    
    public function headings():array{
        return [ 'logo', 'name', 'phone', 'email', 'address'];
    }
    public function map($branch):array{
        return [
            $branch->logo,
            $branch->name,
            $branch->phone,
            $branch->email ?? 'N/A',
            $branch->address,
        ];
    }
}
