<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'tbl_sales'; // Change to your actual table name if different

    public $timestamps = false; // Set to true if you use created_at/updated_at

    protected $fillable = [
        'reference_no',
        'branch_id',
        'customer_id',
        'date',
        'grand_total',
        'paid_amount',
        'balance',
        'payment_status',
    ];

    // Branch Relationship
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    // Customer Relationship
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    // Items Relationship
    public function items()
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }
}