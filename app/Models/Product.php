<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";


     protected $fillable = [
        'category_id',
        'name',
        'image',
        'description',
        'brand_name',
        'price',
        'dummy_price',
    ];

    // Cast the image field as an array since it is stored as JSON
    protected $casts = [
        'image' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
