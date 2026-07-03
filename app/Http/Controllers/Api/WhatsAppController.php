<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Services\NotificationService;
use App\Services\QueueService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $whatsAppService;
    protected $notificationService;
    protected $queueService;

    public function __construct(WhatsAppService $whatsAppService, NotificationService $notificationService, QueueService $queueService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->notificationService = $notificationService;
        $this->queueService = $queueService;
    }

    /**
     * GET /api/whatsapp/cron?cron_key=...
     */
    public function cron(Request $request)
    {
        $cronKey = config('klinik.wa_cron_key', 'klinikgenz_cron_2024');
        $incomingKey = $request->cron_key;

        $isAdmin = auth()->check() && auth()->user()->role === 'admin';

        if (!$isAdmin && $incomingKey !== $cronKey) {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $results = ['notified' => 0, 'marked_late' => 0, 'errors' => []];

        // 1. Kirim WA reminder 10 menit sebelum
        $notifyMinutes = config('klinik.queue_notify_minutes', 10);
        $now = now();
        $notifyFrom = $now->copy()->addMinute()->format('H:i:s');
        $notifyTo = $now->copy()->addMinutes($notifyMinutes)->format('H:i:s');

        $upcoming = Queue::with(['patient', 'poly'])
            ->whereDate('queue_date', today())
            ->where('status', 'waiting')
            ->whereNotNull('estimated_time')
            ->whereBetween('estimated_time', [$notifyFrom, $notifyTo])
            ->whereDoesntHave('notifications', function ($q) {
                $q->where('type', 'queue_call')
                    ->whereDate('created_at', today());
            })
            ->get();

        foreach ($upcoming as $queue) {
            $message = "⏰ *Pengingat Antrian — Klinik Gen-Z*\n\n"
                . "Halo *{$queue->patient->username}*!\n"
                . "Nomor antrian *#{$queue->queue_number}* di *{$queue->poly->name}* akan dipanggil sekitar *"
                . date('H:i', strtotime($queue->estimated_time)) . "* (±10 menit lagi).\n\n"
                . "Mohon bersiap di area tunggu. Jangan sampai terlambat! 🏥";

            $sent = $this->whatsAppService->send($queue->patient->phone, $message);

            $this->notificationService->create(
                $queue->patient_id,
                'queue_call',
                'Antrian Segera Dipanggil!',
                "Nomor #{$queue->queue_number} di {$queue->poly->name} akan dipanggil ±10 menit lagi.",
                $queue->id
            );

            if ($sent) {
                $results['notified']++;
            } else {
                $results['errors'][] = "Failed WA to {$queue->patient->phone}";
            }
        }

        // 2. Penanganan late
        $lateThreshold = config('klinik.queue_late_minutes', 15);
        $lateTime = now()->subMinutes($lateThreshold)->format('Y-m-d H:i:s');

        $lateQueues = Queue::with(['patient', 'poly', 'doctor', 'schedule'])
            ->whereDate('queue_date', today())
            ->where('status', 'called')
            ->where('called_at', '<=', $lateTime)
            ->where('late_handled', false)
            ->get();

        foreach ($lateQueues as $queue) {
            $this->queueService->handleLate($queue);
            $results['marked_late']++;
        }

        return response()->json([
            'success' => true,
            'timestamp' => now()->toDateTimeString(),
            'notified' => $results['notified'],
            'marked_late' => $results['marked_late'],
            'errors' => $results['errors'],
        ]);
    }
}