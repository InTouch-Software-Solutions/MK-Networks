<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignRoute extends Model
{
    protected $table = 'assign_routes';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'user_id',
        'route_id',
        'shop_name',
        'address',
        'route',
        'area',
    ];
}
