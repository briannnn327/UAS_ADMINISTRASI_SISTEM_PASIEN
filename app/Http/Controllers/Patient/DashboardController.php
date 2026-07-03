<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Survey;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard pasien.
     */
    public function index()
    {
        $user = Auth::user();

        // Antrian aktif hari ini
        $activeQueue = Queue::with(['poly', 'doctor.user'])
            ->where('patient_id', $user->id)
            ->whereDate('queue_date', today())
            ->whereNotIn('status', ['done', 'cancelled'])
            ->first();

        // Total kunjungan
        $totalQueues = Queue::where('patient_id', $user->id)->count();

        // Total survei
        $totalSurveys = Survey::where('patient_id', $user->id)->count();

        // Survei yang belum diisi (status done tapi belum ada survei)
        $pendingSurvey = Queue::where('patient_id', $user->id)
            ->where('status', 'done')
            ->whereDoesntHave('survey')
            ->first();

        // 3 antrian terakhir
        $recentQueues = Queue::with('poly')
            ->where('patient_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        return view('patient.dashboard', compact(
            'activeQueue',
            'totalQueues',
            'totalSurveys',
            'pendingSurvey',
            'recentQueues'
        ));
    }
}