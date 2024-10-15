<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    protected $table = 'users';

    use HasApiTokens, HasFactory, Notifiable;

   
    protected $fillable = [
        'name',
        'email',
        'password',
        'token',
        'role',
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function salesman()
    {
        return $this->hasOne(Salesman::class);
    }
}
