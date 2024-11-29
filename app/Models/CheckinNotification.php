<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckinNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'salesman_id',
        'street',
        'city',
        'postcode',
        'date',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
        'date' => 'date',    
    ];
}
