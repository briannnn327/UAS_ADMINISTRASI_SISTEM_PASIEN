<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'poly_id', 'specialization', 'license_number', 'bio', 'is_available'
    ];

    protected $casts = ['is_available' => 'boolean'];

    // Relasi: dokter milik user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: dokter bekerja di poli
    public function poly()
    {
        return $this->belongsTo(Poly::class);
    }

    // Relasi: dokter memiliki banyak jadwal
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    // Relasi: dokter menangani banyak antrian
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}