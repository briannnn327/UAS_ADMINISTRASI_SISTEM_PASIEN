<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'username', 'email', 'phone', 'password', 'role', 'is_active',
        'reset_token', 'reset_expires'
    ];

    protected $hidden = ['password', 'remember_token', 'reset_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relasi: satu user bisa menjadi satu dokter (jika role doctor)
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    // Relasi: pasien memiliki banyak antrian
    public function queues()
    {
        return $this->hasMany(Queue::class, 'patient_id');
    }

    // Relasi: notifikasi milik user
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Relasi: survei milik pasien
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'patient_id');
    }

    // Cek role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isPatient(): bool
    {
        return $this->role === 'patient';
    }
}