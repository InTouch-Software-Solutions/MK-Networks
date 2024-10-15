<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
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
