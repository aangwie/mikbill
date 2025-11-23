<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
//use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Kita ganti $fillable menjadi $guarded = []
     * Artinya semua kolom (name, email, password, role) BISA diisi.
     */
    protected $guarded = []; 

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relasi ke Customer (Operator membawahi banyak pelanggan)
    public function customers()
    {
        return $this->hasMany(Customer::class, 'operator_id');
    }
}