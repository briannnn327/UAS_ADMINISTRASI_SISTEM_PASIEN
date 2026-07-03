<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Schedule;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Dashboard dokter.
     */
    public function index()
    {
        $user = Auth::user();
        $doctor = $user->doctor;

        if (!$doctor) {
            return redirect()->route('doctor.dashboard')
                ->with('error', 'Data dokter tidak ditemukan.');
        }

        // Antrian hari ini
        $todayQueues = Queue::with('patient')
            ->where('doctor_id', $doctor->id)
            ->whereDate('queue_date', today())
            ->orderBy('queue_number')
            ->get();

        $stats = [
            'waiting' => $todayQueues->where('status', 'waiting')->count(),
            'called'  => $todayQueues->whereIn('status', ['called', 'in_progress'])->count(),
            'done'    => $todayQueues->where('status', 'done')->count(),
            'total'   => $todayQueues->count(),
        ];

        // Jadwal hari ini
        $todaySchedule = Schedule::where('doctor_id', $doctor->id)
            ->where('day_of_week', today()->dayOfWeek)
            ->where('is_available', true)
            ->first();

        // Semua jadwal dokter untuk toggle
        $schedules = Schedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('doctor.dashboard', compact('todayQueues', 'stats', 'todaySchedule', 'schedules'));
    }
}