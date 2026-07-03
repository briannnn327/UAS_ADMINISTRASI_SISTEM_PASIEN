<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'queue_id', 'type', 'title', 'message', 'is_read'];

    protected $casts = ['is_read' => 'boolean'];

    // Relasi: notifikasi milik user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: notifikasi terkait antrian
    public function queue()
    {
        return $this->belongsTo(Queue::class);
    }
}