<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduleController extends Controller
{
    /**
     * Tampilan jadwal saya.
     */
    public function index()
    {
        $doctor = Auth::user()->doctor;

        $schedules = Schedule::where('doctor_id', $doctor->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Hitung antrian 7 hari ke depan per hari
        $queueCounts = Queue::where('doctor_id', $doctor->id)
            ->whereDate('queue_date', '>=', today())
            ->whereDate('queue_date', '<=', today()->addDays(7))
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('DAYOFWEEK(queue_date) - 1 as dow, COUNT(*) as total')
            ->groupBy('dow')
            ->pluck('total', 'dow')
            ->toArray();

        return view('doctor.schedule', compact('schedules', 'queueCounts'));
    }

    /**
     * Toggle ketersediaan jadwal (hanya milik sendiri).
     */
    public function toggle(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
        ]);

        $schedule = Schedule::find($request->schedule_id);
        $doctor = Auth::user()->doctor;

        if ($schedule->doctor_id !== $doctor->id) {
            return back()->with('error', 'Anda tidak memiliki akses ke jadwal ini.');
        }

        $schedule->update(['is_available' => !$schedule->is_available]);

        $status = $schedule->is_available ? 'Buka Slot' : 'Tutup Slot';
        return back()->with('success', "Status jadwal: {$status}");
    }
}