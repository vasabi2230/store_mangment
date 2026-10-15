<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'tbl_categories'; //[cite: 1]
    public $timestamps = false;
    
    protected $fillable = ['name', 'description','code'];
    // Define relationship to Category model
  
}