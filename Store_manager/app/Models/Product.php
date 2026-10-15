<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'tbl_products';

    // Disable automatic timestamps for legacy tbl_ schema
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
        'category_id',
        'unit_id',
        'supplier_id',
        'price',
        'cost',
        'quantity',
        'image',
    ];

   public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id', 'id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}