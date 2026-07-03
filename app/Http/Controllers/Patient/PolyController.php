<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use Illuminate\Http\Request;

class PolyController extends Controller
{
    /**
     * Halaman info poli & jadwal.
     */
    public function index(Request $request)
    {
        $polies = Poly::where('is_active', true)->orderBy('id')->get();
        $selectedId = (int) ($request->id ?? $polies->first()->id ?? 0);

        $selectedPoly = $polies->firstWhere('id', $selectedId);

        if (!$selectedPoly) {
            return redirect()->route('patient.poli-info');
        }

        // Dokter aktif di poli tersebut
        $doctors = $selectedPoly->doctors()
            ->with('user')
            ->where('is_available', true)
            ->get();

        // Jadwal per dokter
        $schedules = [];
        foreach ($doctors as $doctor) {
            $schedules[$doctor->id] = $doctor->schedules()
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();
        }

        // Antrian hari ini per poli
        $queueCounts = Queue::whereDate('queue_date', today())
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw('poly_id, COUNT(*) as total')
            ->groupBy('poly_id')
            ->pluck('total', 'poly_id')
            ->toArray();

        return view('patient.poli-info', compact(
            'polies',
            'selectedPoly',
            'doctors',
            'schedules',
            'queueCounts'
        ));
    }
}