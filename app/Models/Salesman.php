<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Salesman extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "salesmen";
    protected $fillable = [
        'user_id',
        'phone_number',
        'area',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
