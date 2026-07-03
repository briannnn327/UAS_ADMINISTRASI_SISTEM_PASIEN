<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_id', 'patient_id', 'doctor_rating', 'service_rating',
        'facility_rating', 'overall_rating', 'comments', 'wa_thanks_sent'
    ];

    protected $casts = ['wa_thanks_sent' => 'boolean'];

    // Relasi: survei dari antrian
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }

    // Relasi: survei milik pasien
    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
}