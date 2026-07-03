<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class QueueService
{
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(
        NotificationService $notificationService,
        WhatsAppService $whatsAppService
    ) {
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Generate nomor antrian berikutnya.
     */
    public function nextNumber(int $polyId, int $doctorId, string $date): int
    {
        $max = Queue::where('poly_id', $polyId)
            ->where('doctor_id', $doctorId)
            ->whereDate('queue_date', $date)
            ->whereNotIn('status', ['cancelled'])
            ->max('queue_number');

        return (int) $max + 1;
    }

    /**
     * Estimasi waktu berdasarkan nomor antrian.
     */
    public function estimateTime(int $scheduleId, int $queueNumber): string
    {
        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            return '00:00';
        }

        $start = strtotime($schedule->start_time);
        $end = strtotime($schedule->end_time);
        $slots = max(1, (int) $schedule->max_slots);
        $interval = ($end - $start) / $slots;

        return date('H:i', (int) ($start + $interval * ($queueNumber - 1)));
    }

    /**
     * Hitung jumlah antrian menunggu di poli.
     */
    public function waitingCount(int $polyId, string $date): int
    {
        return Queue::where('poly_id', $polyId)
            ->whereDate('queue_date', $date)
            ->whereIn('status', ['waiting', 'called'])
            ->count();
    }

    /**
     * Tangani antrian terlambat.
     * Jika poli ramai → hangus, jika tidak ramai → digeser ke slot terakhir.
     */
    public function handleLate(Queue $queue): void
    {
        $busyThreshold = config('klinik.queue_busy_threshold', 10);
        $waiting = $this->waitingCount($queue->poly_id, $queue->queue_date);

        DB::transaction(function () use ($queue, $waiting, $busyThreshold) {
            if ($waiting >= $busyThreshold) {
                // Poli ramai → hangus
                $queue->update([
                    'status' => 'cancelled',
                    'late_handled' => true,
                ]);

                $this->notificationService->create(
                    $queue->patient_id,
                    'queue_late',
                    'Antrian Hangus',
                    "Antrian #{$queue->queue_number} di {$queue->poly->name} hangus karena terlambat dan poli ramai.",
                    $queue->id
                );

                $message = "⚠️ *Antrian Hangus — Klinik Gen-Z*\n\n"
                    . "Maaf *{$queue->patient->username}*, antrian *#{$queue->queue_number}* di *{$queue->poly->name}* telah "
                    . "*hangus* karena keterlambatan dan poli sedang ramai.\n\n"
                    . "Silakan ambil nomor antrian baru atau datang di hari lain.";

                $this->whatsAppService->send($queue->patient->phone, $message);
            } else {
                // Tidak ramai → geser ke slot terakhir
                $newNumber = $this->nextNumber($queue->poly_id, $queue->doctor_id, $queue->queue_date);
                $newTime = $this->estimateTime($queue->schedule_id, $newNumber);

                $queue->update([
                    'status' => 'rescheduled',
                    'queue_number' => $newNumber,
                    'estimated_time' => $newTime,
                    'late_handled' => true,
                ]);

                $this->notificationService->create(
                    $queue->patient_id,
                    'queue_late',
                    'Antrian Digeser',
                    "Antrian Anda digeser ke nomor #{$newNumber} (est. {$newTime}) karena keterlambatan.",
                    $queue->id
                );

                $message = "ℹ️ *Info Antrian — Klinik Gen-Z*\n\n"
                    . "Halo *{$queue->patient->username}*, karena keterlambatan, antrian Anda di *{$queue->poly->name}* "
                    . "telah digeser ke nomor *#{$newNumber}* (estimasi jam *{$newTime}*).\n\n"
                    . "Poli tidak terlalu ramai, Anda masih bisa dilayani. Segera hadir! 🏥";

                $this->whatsAppService->send($queue->patient->phone, $message);
            }
        });
    }

    /**
     * Kirim notifikasi survei setelah antrian selesai.
     */
    public function sendSurveyNotification(Queue $queue): void
    {
        $surveyUrl = route('survey.form', $queue->id);

        $this->notificationService->create(
            $queue->patient_id,
            'survey_request',
            'Isi Survei Kepuasan',
            'Pemeriksaan selesai! Mohon luangkan waktu mengisi survei kepuasan Anda.',
            $queue->id
        );

        $message = "✅ Terima kasih *{$queue->patient->username}* telah berkunjung ke Klinik Gen-Z!\n\n"
            . "Mohon isi survei kepuasan kami:\n👉 {$surveyUrl}\n\n"
            . "Penilaian Anda sangat berarti bagi kami 🙏";

        $this->whatsAppService->send($queue->patient->phone, $message);
    }
}