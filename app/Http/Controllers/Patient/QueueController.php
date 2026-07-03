<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Http\Requests\PatientRegisterQueueRequest;
use App\Models\Doctor;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\Schedule;
use App\Services\NotificationService;
use App\Services\QueueService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    protected $queueService;
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(
        QueueService $queueService,
        NotificationService $notificationService,
        WhatsAppService $whatsAppService
    ) {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Form pendaftaran antrian.
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Cek apakah sudah punya antrian aktif hari ini
        $activeQueue = Queue::where('patient_id', $user->id)
            ->whereDate('queue_date', today())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->first();

        $polies = Poly::where('is_active', true)->get();
        $doctors = Doctor::with('user', 'poly')
            ->where('is_available', true)
            ->get();

        // Pre-select doctor jika ada parameter
        $selectedDoctorId = (int) $request->doctor_id;

        return view('patient.register-queue', compact(
            'polies',
            'doctors',
            'activeQueue',
            'selectedDoctorId'
        ));
    }

    /**
     * Proses pendaftaran antrian.
     */
    public function store(PatientRegisterQueueRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();

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

        // Ambil data jadwal
        $schedule = Schedule::with('doctor.poly')->find($validated['schedule_id']);
        if (!$schedule || !$schedule->is_available) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak tersedia.'
            ], 422);
        }

        // Cek slot tersisa
        $usedCount = Queue::where('schedule_id', $schedule->id)
            ->whereDate('queue_date', $validated['queue_date'])
            ->whereNotIn('status', ['cancelled'])
            ->count();

        if ($usedCount >= $schedule->max_slots) {
            return response()->json([
                'success' => false,
                'message' => 'Slot antrian sudah penuh untuk jadwal ini.'
            ], 422);
        }

        // Generate nomor antrian & estimasi waktu
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
            'patient_id'     => $user->id,
            'poly_id'        => $schedule->doctor->poly_id,
            'doctor_id'      => $schedule->doctor_id,
            'schedule_id'    => $schedule->id,
            'queue_number'   => $queueNumber,
            'queue_date'     => $validated['queue_date'],
            'estimated_time' => $estimatedTime,
            'status'         => 'waiting',
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

    /**
     * Batalkan antrian (via AJAX dari history).
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:queues,id',
        ]);

        $user = Auth::user();
        $queue = Queue::with('poly')->find($request->queue_id);

        if ($queue->patient_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($queue->status !== 'waiting') {
            return response()->json(['success' => false, 'message' => 'Antrian tidak dapat dibatalkan.'], 422);
        }

        $queue->update(['status' => 'cancelled']);

        $this->notificationService->create(
            $user->id,
            'general',
            'Antrian Dibatalkan',
            "Antrian #{$queue->queue_number} di {$queue->poly->name} telah dibatalkan.",
            $queue->id
        );

        $message = "❌ *Antrian Dibatalkan*\n\n"
            . "Nomor antrian *#{$queue->queue_number}* di *{$queue->poly->name}* telah dibatalkan.\n\n"
            . "Silakan daftar ulang jika Anda masih membutuhkan layanan.\n"
            . "_Klinik Gen-Z_";

        $this->whatsAppService->send($user->phone, $message);

        return response()->json(['success' => true, 'message' => 'Antrian berhasil dibatalkan.']);
    }
}