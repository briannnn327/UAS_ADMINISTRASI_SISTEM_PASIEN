<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Poly;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueHistoryController extends Controller
{
    /**
     * Riwayat antrian dengan filter role-based.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Queue::with(['patient', 'poly', 'doctor.user', 'survey']);

        // Filter berdasarkan role
        if ($user->role === 'patient') {
            $query->where('patient_id', $user->id);
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $query->where('doctor_id', $doctor->id);
            } else {
                return redirect()->route('home')->with('error', 'Data dokter tidak ditemukan.');
            }
        }
        // Admin: lihat semua

        // Filter tambahan
        $filterDate = $request->date;
        $filterPoly = (int) $request->poly_id;
        $filterStatus = $request->status;

        if ($filterDate) {
            $query->whereDate('queue_date', $filterDate);
        }
        if ($filterPoly) {
            $query->where('poly_id', $filterPoly);
        }
        if ($filterStatus) {
            $query->where('status', $filterStatus);
        }

        $queues = $query->orderBy('queue_date', 'desc')
            ->orderBy('queue_number')
            ->limit(100)
            ->get();

        $polies = Poly::where('is_active', true)->get();

        // Antrian aktif untuk pasien
        $activeQueue = null;
        if ($user->role === 'patient') {
            $activeQueue = Queue::where('patient_id', $user->id)
                ->whereDate('queue_date', today())
                ->whereNotIn('status', ['done', 'cancelled'])
                ->first();
        }

        return view('queue.history', compact(
            'queues',
            'polies',
            'filterDate',
            'filterPoly',
            'filterStatus',
            'activeQueue'
        ));
    }
}