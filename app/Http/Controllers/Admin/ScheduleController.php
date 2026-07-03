<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Doctor;
use App\Models\Queue;
use App\Models\Schedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    /**
     * Daftar jadwal.
     */
    public function index()
    {
        $schedules = Schedule::with(['doctor.user', 'doctor.poly'])
            ->orderBy('doctor_id')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        // Hitung antrian terpakai hari ini per schedule
        $usedCounts = Queue::whereDate('queue_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('schedule_id, count(*) as total')
            ->groupBy('schedule_id')
            ->pluck('total', 'schedule_id')
            ->toArray();

        $doctors = Doctor::with('user', 'poly')->get();
        return view('admin.schedules.index', compact('schedules', 'doctors', 'usedCounts'));
    }

    /**
     * Simpan jadwal baru.
     */
    public function store(StoreScheduleRequest $request)
    {
        $validated = $request->validated();
        Schedule::create($validated);
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil ditambahkan.');
    }

    /**
     * Edit jadwal.
     */
    public function edit(Schedule $schedule)
    {
        $doctors = Doctor::with('user', 'poly')->get();
        return view('admin.schedules.edit', compact('schedule', 'doctors'));
    }

    /**
     * Update jadwal.
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule)
    {
        $validated = $request->validated();
        $schedule->update($validated);
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil diperbarui.');
    }

    /**
     * Toggle ketersediaan jadwal.
     */
    public function toggleAvailability(Schedule $schedule)
    {
        $schedule->update(['is_available' => !$schedule->is_available]);
        $status = $schedule->is_available ? 'Aktif' : 'Nonaktif';
        return back()->with('success', "Status jadwal: {$status}");
    }

    /**
     * Hapus jadwal.
     */
    public function destroy(Schedule $schedule)
    {
        $schedule->delete();
        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil dihapus.');
    }
}