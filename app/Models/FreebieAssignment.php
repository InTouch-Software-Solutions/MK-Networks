<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreebieAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'salesman_id', 'quantity'];

}