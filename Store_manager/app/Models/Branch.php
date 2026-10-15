<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'tbl_branches'; // Map to custom table name[cite: 1]
    public $timestamps = false; // Disable default Laravel timestamps

    protected $fillable = ['logo', 'name', 'phone', 'address', 'email'];
}