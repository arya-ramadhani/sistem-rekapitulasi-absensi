<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nip',
        'email',
        'password',
        'role',
        'profile_photo_path',
        'no_telp',
        'jenis_kelamin',
        'alamat',
        'agama',
        'tanggal_lahir'
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
        'password' => 'hashed',
    ];

    /**
     * Check if user is admin
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is dosen
     *
     * @return boolean
     */
    public function isDosen()
    {
        return $this->role === 'dosen';
    }

    /**
     * Get user role
     *
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Check if user has specific role
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->role === $role;
    }

    /**
     * Relasi dengan kelas (untuk dosen)
     */
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'id_dosen', 'nip');
    }

    /**
     * Get name attribute untuk kompatibilitas
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->nama_lengkap;
    }

    /**
     * Mutator untuk tanggal lahir
     */
    public function setTanggalLahirAttribute($value)
    {
        $this->attributes['tanggal_lahir'] = $value ? $value : null;
    }
}