<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $table = 'tbl_warehouses';

    // tbl_warehouses has no created_at / updated_at columns.
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'name',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'warehouse_id');
    }
}