<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SurveyController extends Controller
{
    /**
     * Halaman grafik survei.
     */
    public function chart()
    {
        $user = Auth::user();

        // Statistik global
        $global = Survey::select(
            DB::raw('ROUND(AVG(doctor_rating), 2) as avg_doc'),
            DB::raw('ROUND(AVG(service_rating), 2) as avg_svc'),
            DB::raw('ROUND(AVG(facility_rating), 2) as avg_fac'),
            DB::raw('ROUND(AVG(overall_rating), 2) as avg_all'),
            DB::raw('COUNT(*) as total')
        )->first();

        // === PERBAIKAN: Tren 6 bulan ===
        // Gunakan MIN(created_at) untuk mendapatkan tanggal perwakilan di setiap grup
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

        // Distribusi rating (sudah benar)
        $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $distData = Survey::select('overall_rating', DB::raw('COUNT(*) as n'))
            ->groupBy('overall_rating')
            ->pluck('n', 'overall_rating')
            ->toArray();
        foreach ($distData as $rating => $count) {
            $dist[(int) $rating] = $count;
        }

        // Kepuasan per poli (sudah benar)
        $perPoli = Poly::withCount(['queues as survey_count' => function ($q) {
            $q->join('surveys', 'queues.id', '=', 'surveys.queue_id');
        }])
            ->withAvg(['queues as avg_rating' => function ($q) {
                $q->join('surveys', 'queues.id', '=', 'surveys.queue_id')
                    ->select(DB::raw('ROUND(AVG(overall_rating), 2)'));
            }], 'overall_rating')
            ->get();

        // Survei saya
        $mySurveys = Survey::with(['queue.poly'])
            ->where('patient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Survei yang belum diisi
        $pendingSurveys = Queue::with('poly')
            ->where('patient_id', $user->id)
            ->where('status', 'done')
            ->whereDoesntHave('survey')
            ->orderBy('queue_date', 'desc')
            ->get();

        return view('patient.survey-chart', compact(
            'global',
            'trend',
            'dist',
            'perPoli',
            'mySurveys',
            'pendingSurveys'
        ));
    }
}