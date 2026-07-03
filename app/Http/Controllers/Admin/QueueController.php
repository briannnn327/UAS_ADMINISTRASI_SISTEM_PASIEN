<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\QueueService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

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
     * Menampilkan daftar antrian dengan filter.
     */
    public function index(Request $request)
    {
        $date   = $request->date ?? today()->toDateString();
        $polyId = (int) $request->poly_id;
        $status = $request->status;

        $query = Queue::with(['patient', 'poly', 'doctor.user']);

        if ($date) {
            $query->whereDate('queue_date', $date);
        }
        if ($polyId) {
            $query->where('poly_id', $polyId);
        }
        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('queue_number')->get();
        $polies = Poly::where('is_active', true)->get();

        return view('admin.queues.index', compact('queues', 'polies', 'date', 'polyId', 'status'));
    }

    /**
     * Panggil antrian.
     */
    public function call(Request $request)
    {
        $queueId = (int) $request->queue_id;
        $queue = Queue::with(['patient', 'poly'])->find($queueId);

        if (!$queue) {
            return back()->with('error', 'Antrian tidak ditemukan.');
        }

        // Update status ke 'called'
        $queue->update([
            'status' => 'called',
            'called_at' => now(),
        ]);

        // Notifikasi in-app
        $this->notificationService->create(
            $queue->patient_id,
            'queue_call',
            'Antrian Dipanggil!',
            "Nomor antrian #{$queue->queue_number} di {$queue->poly->name} dipanggil. Segera hadir.",
            $queue->id
        );

        // Kirim WA
        $message = "🔔 *{$queue->poly->name}*\n\n"
            . "Nomor antrian *#{$queue->queue_number}* atas nama *{$queue->patient->username}* sudah dipanggil!\n\n"
            . "Mohon segera hadir ke loket pemeriksaan.";

        $this->whatsAppService->send($queue->patient->phone, $message);

        return back()->with('success', "Antrian #{$queue->queue_number} berhasil dipanggil.");
    }

    /**
     * Update status antrian.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'status'   => 'required|in:waiting,called,in_progress,done,cancelled,late,rescheduled',
        ]);

        $queue = Queue::with(['patient', 'poly', 'doctor.user', 'schedule'])->find($request->queue_id);
        $newStatus = $request->status;

        // Jika status menjadi 'done', kirim notifikasi survei
        if ($newStatus === 'done') {
            $surveyUrl = route('survey.form', $queue->id);
            $this->notificationService->create(
                $queue->patient_id,
                'survey_request',
                'Isi Survei Kepuasan',
                'Pemeriksaan selesai! Mohon luangkan waktu mengisi survei kepuasan Anda.',
                $queue->id
            );

            $message = "✅ Terima kasih *{$queue->patient->username}* telah berkunjung!\n\n"
                . "Mohon isi survei kepuasan kami:\n👉 {$surveyUrl}\n\n"
                . "Penilaian Anda sangat berarti bagi kami 🙏";

            $this->whatsAppService->send($queue->patient->phone, $message);
        }

        // Jika status menjadi 'late', tangani keterlambatan
        if ($newStatus === 'late') {
            $this->queueService->handleLate($queue);
            return back()->with('success', 'Antrian dihandle (late).');
        }

        $queue->update(['status' => $newStatus]);

        return back()->with('success', 'Status antrian berhasil diperbarui.');
    }
}