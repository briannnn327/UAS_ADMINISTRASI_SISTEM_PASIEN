<?php

namespace App\Http\Controllers;

use App\Models\Queue;
use App\Models\Survey;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class SurveyFormController extends Controller
{
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(NotificationService $notificationService, WhatsAppService $whatsAppService)
    {
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Tampilkan form survei.
     */
    public function create(Queue $queue)
    {
        // Validasi: status harus done
        if ($queue->status !== 'done') {
            return redirect()->route('patient.dashboard')
                ->with('error', 'Survei tidak tersedia. Antrian belum selesai.');
        }

        // Cek duplikat survei
        $existing = Survey::where('queue_id', $queue->id)->exists();
        if ($existing) {
            return view('survey.already-filled', compact('queue'));
        }

        $queue->load(['patient', 'poly', 'doctor.user']);
        return view('survey.form', compact('queue'));
    }

    /**
     * Simpan survei.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'queue_id'         => 'required|exists:queues,id',
            'patient_id'       => 'required|exists:users,id',
            'doctor_rating'    => 'required|integer|min:1|max:5',
            'service_rating'   => 'required|integer|min:1|max:5',
            'facility_rating'  => 'required|integer|min:1|max:5',
            'overall_rating'   => 'required|integer|min:1|max:5',
            'comments'         => 'nullable|string',
        ]);

        $queue = Queue::with(['patient', 'poly'])->find($validated['queue_id']);

        // Cek duplikat
        if (Survey::where('queue_id', $queue->id)->exists()) {
            return redirect()->route('survey.results', ['submitted' => 1, 'name' => $queue->patient->username])
                ->with('error', 'Survei sudah pernah diisi.');
        }

        // Simpan survei
        $survey = Survey::create([
            'queue_id'         => $validated['queue_id'],
            'patient_id'       => $validated['patient_id'],
            'doctor_rating'    => $validated['doctor_rating'],
            'service_rating'   => $validated['service_rating'],
            'facility_rating'  => $validated['facility_rating'],
            'overall_rating'   => $validated['overall_rating'],
            'comments'         => $validated['comments'] ?? null,
            'wa_thanks_sent'   => false,
        ]);

        // Notifikasi in-app
        $this->notificationService->create(
            $queue->patient_id,
            'survey_thanks',
            'Terima Kasih!',
            'Terima kasih telah mengisi survei kepuasan kami. Penilaian Anda sangat berarti!',
            $queue->id
        );

        // Kirim WA terima kasih
        $avg = round(($validated['doctor_rating'] + $validated['service_rating'] + $validated['facility_rating'] + $validated['overall_rating']) / 4, 1);
        $message = "🙏 Terima kasih *{$queue->patient->username}* telah mengisi survei kepuasan di *Klinik Gen-Z*!\n\n"
            . "Poli: {$queue->poly->name}\n"
            . "Rating Anda: *{$avg}/5*\n\n"
            . "Masukan Anda sangat membantu kami untuk terus meningkatkan kualitas pelayanan. Sampai jumpa di kunjungan berikutnya! 😊";

        $this->whatsAppService->send($queue->patient->phone, $message);

        // Tandai WA sudah terkirim
        $survey->update(['wa_thanks_sent' => true]);

        return redirect()->route('survey.results', [
            'submitted' => 1,
            'name' => $queue->patient->username
        ]);
    }
}