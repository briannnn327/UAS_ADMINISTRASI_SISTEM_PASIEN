<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Queue extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'poly_id', 'doctor_id', 'schedule_id',
        'queue_number', 'queue_date', 'estimated_time', 'status',
        'called_at', 'late_handled'
    ];

    protected $casts = [
        'queue_date' => 'date',
        'called_at' => 'datetime',
        'late_handled' => 'boolean',
    ];

    // Relasi: antrian milik pasien
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    // Relasi: antrian di poli
    public function poly()
    {
        return $this->belongsTo(Poly::class);
    }

    // Relasi: antrian ditangani dokter
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    // Relasi: antrian berdasarkan jadwal
    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    // Relasi: antrian memiliki satu survei
    public function survey()
    {
        return $this->hasOne(Survey::class);
    }

    // Relasi: antrian memiliki notifikasi terkait
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Helper: status meta (label + class)
    public function getStatusMetaAttribute(): array
    {
        $meta = [
            'waiting'     => ['label' => 'Menunggu', 'tw' => 'bg-yellow-100 text-yellow-800'],
            'called'      => ['label' => 'Dipanggil', 'tw' => 'bg-blue-100 text-blue-800'],
            'in_progress' => ['label' => 'Dalam Pemeriksaan', 'tw' => 'bg-indigo-100 text-indigo-800'],
            'done'        => ['label' => 'Selesai', 'tw' => 'bg-green-100 text-green-800'],
            'late'        => ['label' => 'Terlambat', 'tw' => 'bg-red-100 text-red-800'],
            'cancelled'   => ['label' => 'Dibatalkan', 'tw' => 'bg-gray-100 text-gray-600'],
            'rescheduled' => ['label' => 'Dijadwalkan Ulang', 'tw' => 'bg-purple-100 text-purple-800'],
        ];
        return $meta[$this->status] ?? ['label' => $this->status, 'tw' => 'bg-gray-100 text-gray-600'];
    }

    // Helper: posisi antrian
    public function getPositionAttribute(): int
    {
        return Queue::where('poly_id', $this->poly_id)
            ->where('doctor_id', $this->doctor_id)
            ->where('queue_date', $this->queue_date)
            ->whereIn('status', ['waiting', 'called'])
            ->where('queue_number', '<', $this->queue_number)
            ->count() + 1;
    }
}