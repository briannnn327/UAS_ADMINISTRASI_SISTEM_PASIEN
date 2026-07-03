<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Schedule;
use App\Services\NotificationService;
use App\Services\QueueService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueRegisterController extends Controller
{
    protected $queueService;
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(QueueService $queueService, NotificationService $notificationService, WhatsAppService $whatsAppService)
    {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * POST /api/queue-register
     */
    public function store(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'patient') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = Auth::user();

        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'schedule_id' => 'required|exists:schedules,id',
            'queue_date' => 'required|date|after_or_equal:today',
        ]);

        // Cek duplikat antrian aktif
        $existing = Queue::where('patient_id', $user->id)
            ->whereDate('queue_date', $validated['queue_date'])
            ->whereNotIn('status', ['done', 'cancelled'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki antrian aktif pada tanggal tersebut.'
            ], 422);
        }

        // Ambil jadwal
        $schedule = Schedule::with('doctor.poly')->find($validated['schedule_id']);
        if (!$schedule || !$schedule->is_available) {
            return response()->json(['success' => false, 'message' => 'Jadwal tidak tersedia.'], 422);
        }

        // Cek slot tersisa
        $usedCount = Queue::where('schedule_id', $schedule->id)
            ->whereDate('queue_date', $validated['queue_date'])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        if ($usedCount >= $schedule->max_slots) {
            return response()->json(['success' => false, 'message' => 'Slot antrian sudah penuh.'], 422);
        }

        // Generate nomor antrian & estimasi
        $queueNumber = $this->queueService->nextNumber(
            $schedule->doctor->poly_id,
            $schedule->doctor_id,
            $validated['queue_date']
        );

        $estimatedTime = $this->queueService->estimateTime(
            $schedule->id,
            $queueNumber
        );

        // Buat antrian
        $queue = Queue::create([
            'patient_id' => $user->id,
            'poly_id' => $schedule->doctor->poly_id,
            'doctor_id' => $schedule->doctor_id,
            'schedule_id' => $schedule->id,
            'queue_number' => $queueNumber,
            'queue_date' => $validated['queue_date'],
            'estimated_time' => $estimatedTime,
            'status' => 'waiting',
        ]);

        // Notifikasi in-app
        $this->notificationService->create(
            $user->id,
            'general',
            '✅ Antrian Berhasil Didaftarkan',
            "Nomor antrian #{$queueNumber} di {$schedule->doctor->poly->name} "
            . "bersama dr. {$schedule->doctor->user->username}. "
            . "Tanggal: {$validated['queue_date']}. Estimasi jam: {$estimatedTime}.",
            $queue->id
        );

        // Kirim WA
        $message = "✅ *Antrian Berhasil Didaftarkan!*\n\n"
            . "📋 Nomor Antrian : *#{$queueNumber}*\n"
            . "🏥 Poli          : *{$schedule->doctor->poly->name}*\n"
            . "👨‍⚕️ Dokter        : *dr. {$schedule->doctor->user->username}*\n"
            . "📅 Tanggal       : *{$validated['queue_date']}*\n"
            . "🕐 Estimasi Jam  : *{$estimatedTime}*\n\n"
            . "Kami akan mengirim pengingat 10 menit sebelum giliran Anda.\n"
            . "Harap tiba lebih awal dan bawa dokumen yang diperlukan.\n\n"
            . "_Klinik Gen-Z — Klinik Digital_";

        $this->whatsAppService->send($user->phone, $message);

        return response()->json([
            'success' => true,
            'message' => 'Antrian berhasil didaftarkan',
            'queue_number' => $queueNumber,
            'queue_id' => $queue->id,
            'patient_name' => $user->username,
            'poly_name' => $schedule->doctor->poly->name,
            'queue_date' => $validated['queue_date'],
            'estimated_time' => $estimatedTime,
        ]);
    }
}