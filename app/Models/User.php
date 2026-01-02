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
        'plan_expires_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Constants for Roles
    const ROLE_SUPERADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_OPERATOR = 'operator';

    // Helper Methods
    public function isSuperAdmin()
    {
        return $this->role === self::ROLE_SUPERADMIN;
    }

    public function isAdmin()
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isOperator()
    {
        return $this->role === self::ROLE_OPERATOR;
    }

    // Relationships
    // Link ke Parent (Operator -> Admin, Admin -> Superadmin)
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Link ke Children (Admin -> Operators)
    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    // Relasi ke Customer (Operator membawahi banyak pelanggan)
    public function customers()
    {
        return $this->hasMany(Customer::class, 'operator_id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }
}