<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $table = 'tbl_purchases';

    /**
     * IMPORTANT: tbl_purchases has NO created_at / updated_at columns.
     * Without this line every insert fails with "Unknown column 'created_at'".
     */
    public $timestamps = false;

    /**
     * These are the columns that really exist in tbl_purchases.
     * There is NO total_amount column - the real ones are `total` and `grand_total`.
     */
    protected $fillable = [
        'branch_id',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'date',
        'reference_no',
        'total',
        'discount',
        'shipping',
        'grand_total',
        'paid',
        'balance',
        'status',
        'payment_status',
        'note',
    ];

    /**
     * Accessor so that $purchase->total_amount keeps working in your
     * index.blade.php / export() without changing those files.
     */
    public function getTotalAmountAttribute()
    {
        return $this->grand_total ?? $this->total ?? 0;
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }
}