<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_ADMIN = 'ADMIN';
    const ROLE_KITA_MANAGER = 'KITA_MANAGER';
    const ROLE_KITA_STAFF = 'KITA_STAFF';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kita_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function kita()
    {
        return $this->belongsTo(Kita::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKitaManager(): bool
    {
        return $this->role === self::ROLE_KITA_MANAGER;
    }

    public function isKitaStaff(): bool
    {
        return $this->role === self::ROLE_KITA_STAFF;
    }

    public function canManage(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_KITA_MANAGER]);
    }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_KITA_MANAGER => 'Kita-Leitung',
            self::ROLE_KITA_STAFF => 'Kita-Personal',
            default => $this->role,
        };
    }
}
