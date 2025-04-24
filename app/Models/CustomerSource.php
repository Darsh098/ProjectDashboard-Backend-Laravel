<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerSource extends Model
{

    protected $table = 'tbl_customer_source'; // ← Schema.table
    public $timestamps = false;

    protected $fillable = ['title'];
}
