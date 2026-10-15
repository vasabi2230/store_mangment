<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $table = 'tbl_units'; //[cite: 1]
    public $timestamps = false;

    protected $fillable = ['name' , 'description'];
}