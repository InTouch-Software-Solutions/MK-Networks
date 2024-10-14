<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssignVendor extends Model
{
    protected $table = "assign_vendors";

    protected $fillable = ['user_id', 'sim_numbers'];

    protected $casts = [
        'sim_numbers' => 'array',
    ];
}
