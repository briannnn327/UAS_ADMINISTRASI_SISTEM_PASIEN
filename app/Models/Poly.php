<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Poly extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'color', 'icon', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    // Relasi: satu poli memiliki banyak dokter
    public function doctors()
    {
        return $this->hasMany(Doctor::class);
    }

    // Relasi: satu poli memiliki banyak antrian
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }
}