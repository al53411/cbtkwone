<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sekolah_id',
        'name',
        'nip',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Helper Methods Pengecekan Role
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->role === 'superadmin';
    }

    public function isGuru(): bool
    {
        return $this->role === 'guru' || $this->role === 'guru_mapel';
    }

    /**
     * Relasi ke Model Sekolah
     */
    public function sekolah()
    {
        return $this->belongsTo(Sekolah::class, 'sekolah_id');
    }

    /**
     * Relasi ke Model Guru
     */
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id', 'id');
    }

    /**
     * Relasi ke Model JurnalGuru
     */
    public function jurnals()
    {
        return $this->hasMany(JurnalGuru::class, 'guru_id');
    }

    /**
     * Relasi ke Model Ujian (Dibuat/Dimiliki oleh User)
     */
    public function ujians()
    {
        return $this->hasMany(Ujian::class, 'user_id');
    }
}