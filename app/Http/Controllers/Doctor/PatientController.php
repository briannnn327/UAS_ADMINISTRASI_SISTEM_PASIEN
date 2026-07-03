<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(NotificationService $notificationService, WhatsAppService $whatsAppService)
    {
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Daftar pasien hari ini.
     */
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;
        $date = $request->date ?? today()->toDateString();
        $status = $request->status;

        $query = Queue::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereDate('queue_date', $date);

        if ($status) {
            $query->where('status', $status);
        }

        $queues = $query->orderBy('queue_number')->get();

        return view('doctor.patients', compact('queues', 'date', 'status'));
    }

    /**
     * Update status pasien.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'status'   => 'required|in:in_progress,done,late',
        ]);

        $doctor = Auth::user()->doctor;
        $queue = Queue::with(['patient', 'poly'])->find($request->queue_id);

        if ($queue->doctor_id !== $doctor->id) {
            return back()->with('error', 'Anda tidak memiliki akses ke antrian ini.');
        }

        $newStatus = $request->status;

        if ($newStatus === 'done') {
            $surveyUrl = route('survey.form', $queue->id);

            $this->notificationService->create(
                $queue->patient_id,
                'survey_request',
                'Isi Survei Kepuasan',
                'Pemeriksaan selesai! Mohon isi survei kepuasan Anda.',
                $queue->id
            );

            $message = "✅ Terima kasih *{$queue->patient->username}*!\n\n"
                . "Mohon isi survei kepuasan:\n👉 {$surveyUrl}";

            $this->whatsAppService->send($queue->patient->phone, $message);
        }

        $queue->update(['status' => $newStatus]);

        return back()->with('success', 'Status pasien berhasil diperbarui.');
    }
}