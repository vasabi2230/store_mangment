<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'tbl_suppliers'; //[cite: 1]
    public $timestamps = false;

    protected $fillable = ['name', 'phone', 'address', 'email'];
}