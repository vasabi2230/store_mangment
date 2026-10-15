<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'tbl_customers'; //[cite: 1]
    public $timestamps = false;

    protected $fillable = ['name', 'phone', 'address', 'email'];
}