<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAssignment extends Model
{
    use HasFactory;
    protected $fillable = ['product_id', 'salesman_id', 'vendor_id', 'quantity', 'type'];
}
