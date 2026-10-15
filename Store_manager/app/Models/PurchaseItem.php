<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'tbl_purchase_items';

    /**
     * tbl_purchase_items also has NO created_at / updated_at columns.
     */
    public $timestamps = false;

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'unit_quantity',
        'unit_cost',
        'quantity',
        'cost',
        'discount',
        'subtotal',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}