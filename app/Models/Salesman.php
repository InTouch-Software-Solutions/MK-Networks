<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salesman extends Model
{
    protected $table = "salesmen";
    protected $fillable = ['user_id', 'products'];


}
