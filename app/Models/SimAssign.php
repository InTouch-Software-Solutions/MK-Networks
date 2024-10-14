<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SimAssign extends Model
{
    protected $table = "sim_assigns";
    protected $fillable = ['user_id', 'sim_numbers', 'status'];

    protected $casts = [
        'sim_numbers' => 'array',
    ];
}
