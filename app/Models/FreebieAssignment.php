<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreebieAssignment extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'product_id',
        'salesman_id',
        'assigned_quantity',
        'sold_quantity',
        'gifted_quantity',
        'remaining_quantity',
        'threshold',
        'assigned_by',
        'assigned_at'
    ];

}