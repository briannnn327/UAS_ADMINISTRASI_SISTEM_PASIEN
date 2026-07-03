<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * GET /api/schedule/by-doctor?doctor_id=1
     * Ambil jadwal berdasarkan ID dokter.
     */
    public function byDoctor(Request $request)
    {
        $doctorId = (int) $request->doctor_id;

        if (!$doctorId) {
            return response()->json([
                'success' => false,
                'error' => 'Doctor ID required'
            ], 400);
        }

        $schedules = Schedule::where('doctor_id', $doctorId)
            ->where('is_available', 1)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) {
                // Hitung slot terpakai hari ini
                $usedCount = Queue::where('schedule_id', $schedule->id)
                    ->whereDate('queue_date', today())
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                $slotsLeft = max(0, $schedule->max_slots - $usedCount);

                return [
                    'id'          => $schedule->id,
                    'day_of_week' => $schedule->day_of_week,
                    'start_time'  => date('H:i', strtotime($schedule->start_time)),
                    'end_time'    => date('H:i', strtotime($schedule->end_time)),
                    'max_slots'   => $schedule->max_slots,
                    'slots_left'  => $slotsLeft,
                    'notes'       => $schedule->notes,
                ];
            });

        return response()->json([
            'success'   => true,
            'schedules' => $schedules,
        ]);
    }

    /**
     * GET /api/schedule/by-poly?poly_id=1
     */
    public function byPoly(Request $request)
    {
        $polyId = (int) $request->poly_id;

        if (!$polyId) {
            return response()->json([
                'success' => false,
                'error' => 'Poly ID required'
            ], 400);
        }

        $schedules = Schedule::whereHas('doctor', function ($q) use ($polyId) {
            $q->where('poly_id', $polyId);
        })
            ->with(['doctor.user'])
            ->where('is_available', 1)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->map(function ($schedule) {
                $usedCount = Queue::where('schedule_id', $schedule->id)
                    ->whereDate('queue_date', today())
                    ->whereNotIn('status', ['cancelled'])
                    ->count();

                $slotsLeft = max(0, $schedule->max_slots - $usedCount);

                return [
                    'id'          => $schedule->id,
                    'doctor_id'   => $schedule->doctor_id,
                    'doctor_name' => $schedule->doctor->user->username ?? 'Dokter',
                    'day_of_week' => $schedule->day_of_week,
                    'start_time'  => date('H:i', strtotime($schedule->start_time)),
                    'end_time'    => date('H:i', strtotime($schedule->end_time)),
                    'slots_left'  => $slotsLeft,
                ];
            });

        return response()->json([
            'success'   => true,
            'schedules' => $schedules,
        ]);
    }

    /**
     * GET /api/schedule/today?doctor_id=1
     */
    public function today(Request $request)
    {
        $doctorId = (int) $request->doctor_id;
        $dayOfWeek = (int) date('w');

        if (!$doctorId) {
            return response()->json([
                'success' => false,
                'error' => 'Doctor ID required'
            ], 400);
        }

        $schedule = Schedule::where('doctor_id', $doctorId)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', 1)
            ->first();

        return response()->json([
            'success'  => true,
            'schedule' => $schedule,
        ]);
    }

    /**
     * POST /api/schedule/toggle
     */
    public function toggle(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $scheduleId = (int) $request->schedule_id;

        if (!$scheduleId) {
            return response()->json(['success' => false, 'error' => 'Schedule ID required'], 400);
        }

        $schedule = Schedule::find($scheduleId);
        if (!$schedule) {
            return response()->json(['success' => false, 'error' => 'Schedule not found'], 404);
        }

        // Dokter hanya bisa toggle jadwal sendiri
        if ($user->role === 'doctor') {
            $doctor = $user->doctor;
            if (!$doctor || $schedule->doctor_id !== $doctor->id) {
                return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
            }
        }

        $schedule->is_available = !$schedule->is_available;
        $schedule->save();

        return response()->json([
            'success'      => true,
            'is_available' => $schedule->is_available,
        ]);
    }
}