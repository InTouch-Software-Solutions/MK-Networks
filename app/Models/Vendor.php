<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Vendor extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "vendors";
  
    protected $fillable = [
        'user_id',
        'phone_number',
        'address',
        'shop',
        'area',
        'postcode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
