<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\Survey;
use App\Models\User;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $whatsAppService;
    protected $notificationService;

    public function __construct(WhatsAppService $whatsAppService, NotificationService $notificationService)
    {
        $this->whatsAppService = $whatsAppService;
        $this->notificationService = $notificationService;
    }

    /**
     * Tampilan laporan & survei.
     */
    public function index()
    {
        // Statistik agregat
        $avg = Survey::select(
            DB::raw('ROUND(AVG(doctor_rating), 2) as avg_doc'),
            DB::raw('ROUND(AVG(service_rating), 2) as avg_svc'),
            DB::raw('ROUND(AVG(facility_rating), 2) as avg_fac'),
            DB::raw('ROUND(AVG(overall_rating), 2) as avg_all'),
            DB::raw('COUNT(*) as total')
        )->first();

        // === PERBAIKAN: Tren 6 bulan terakhir ===
        // Gunakan MIN(created_at) agar kompatibel dengan only_full_group_by
        $trend = Survey::select(
            DB::raw("DATE_FORMAT(MIN(created_at), '%b %Y') as mon"),
            DB::raw('ROUND(AVG(overall_rating), 2) as avg'),
            DB::raw('COUNT(*) as cnt')
        )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw('MIN(created_at)'), 'desc')
            ->limit(6)
            ->get()
            ->reverse()
            ->values();

        // Distribusi rating
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $distData = Survey::select('overall_rating', DB::raw('COUNT(*) as n'))
            ->groupBy('overall_rating')
            ->pluck('n', 'overall_rating')
            ->toArray();
        foreach ($distData as $rating => $count) {
            $dist[(int) $rating] = $count;
        }

        // Kepuasan per poli
        $perPoli = Poly::withCount(['queues as survey_count' => function ($q) {
            $q->join('surveys', 'queues.id', '=', 'surveys.queue_id');
        }])
            ->withAvg(['queues as avg_rating' => function ($q) {
                $q->join('surveys', 'queues.id', '=', 'surveys.queue_id')
                    ->select(DB::raw('ROUND(AVG(overall_rating), 2)'));
            }], 'overall_rating')
            ->get();

        // Survei terbaru
        $surveys = Survey::with(['patient', 'queue.poly'])
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get();

        return view('admin.reports.index', compact('avg', 'trend', 'dist', 'perPoli', 'surveys'));
    }

    /**
     * Kirim WA terima kasih ke responden survei.
     */
    public function sendThanks(Request $request)
    {
        $survey = Survey::with(['patient', 'queue.poly'])->find($request->survey_id);

        if (!$survey) {
            return back()->with('error', 'Survei tidak ditemukan.');
        }

        if ($survey->wa_thanks_sent) {
            return back()->with('error', 'WA terima kasih sudah pernah dikirim.');
        }

        $message = "🙏 Halo *{$survey->patient->username}*!\n\n"
            . "Terima kasih telah mengisi survei kepuasan di Klinik Gen-Z.\n"
            . "Penilaian Anda sangat berarti bagi kami untuk terus meningkatkan kualitas layanan.\n\n"
            . "Sampai jumpa kembali! 😊";

        $this->whatsAppService->send($survey->patient->phone, $message);

        $survey->update(['wa_thanks_sent' => true]);

        $this->notificationService->create(
            $survey->patient_id,
            'survey_thanks',
            'Terima Kasih!',
            'Terima kasih telah mengisi survei kepuasan. Penilaian Anda sangat berharga!'
        );

        return back()->with('success', "Notifikasi terima kasih berhasil dikirim ke {$survey->patient->username}.");
    }
}