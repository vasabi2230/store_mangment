<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
class CustomersExport implements FromCollection, WithHeadings, WithMapping
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
            return Customer::whereIn('id', $this->selectedIds)->get();
        }
        return Customer::all();
    }
    
    public function headings():array{
        return [ 'ID', 'name', 'phone', 'email', 'address'];
    }
    public function map($customer):array{
        return [
            $customer->id,
            $customer->name,
            $customer->phone,
            $customer->email ?? 'N/A',
            $customer->address,
        ];
    }
}
