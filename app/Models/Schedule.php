<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id', 'day_of_week', 'start_time', 'end_time',
        'max_slots', 'is_available', 'notes'
    ];

    protected $casts = ['is_available' => 'boolean'];

    // Relasi: jadwal milik dokter
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Relasi: jadwal memiliki banyak antrian
    public function queues()
    {
        return $this->hasMany(Queue::class);
    }

    // Helper: nama hari
    public function getDayNameAttribute(): string
    {
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        return $days[$this->day_of_week] ?? '';
    }

    // Helper: slot tersisa
    public function slotsLeft(string $date): int
    {
        $used = $this->queues()
            ->where('queue_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->count();
        return max(0, $this->max_slots - $used);
    }
}