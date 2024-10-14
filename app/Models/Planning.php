<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Planning extends Model
{
    protected $table = 'plannings';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'date',
        'area',
        'shops',
    ];
}
