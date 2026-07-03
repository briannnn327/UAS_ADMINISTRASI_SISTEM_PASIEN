<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HistoryController extends Controller
{
    /**
     * Riwayat pasien dokter.
     */
    public function index(Request $request)
    {
        $doctor = Auth::user()->doctor;

        $from = $request->from ?? today()->subDays(7)->toDateString();
        $to   = $request->to ?? today()->toDateString();
        $status = $request->status;

        $query = Queue::with(['patient', 'survey'])
            ->where('doctor_id', $doctor->id)
            ->whereBetween('queue_date', [$from, $to]);

        if ($status) {
            $query->where('status', $status);
        }

        $history = $query->orderBy('queue_date', 'desc')
            ->orderBy('queue_number')
            ->get();

        return view('doctor.history', compact('history', 'from', 'to', 'status'));
    }
}