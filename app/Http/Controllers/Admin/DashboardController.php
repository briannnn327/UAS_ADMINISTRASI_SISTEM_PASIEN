<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Poly;
use App\Models\Queue;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik ringkas
        $stats = [
            'patients' => User::where('role', 'patient')->count(),
            'doctors'  => User::where('role', 'doctor')->count(),
            'today'    => Queue::whereDate('queue_date', today())->count(),
            'done'     => Queue::whereDate('queue_date', today())->where('status', 'done')->count(),
            'waiting'  => Queue::whereDate('queue_date', today())->where('status', 'waiting')->count(),
            'surveys'  => Survey::count(),
        ];

        // Antrian per poli hari ini
        $polyToday = Poly::withCount([
            'queues as total' => function ($q) {
                $q->whereDate('queue_date', today());
            },
            'queues as done' => function ($q) {
                $q->whereDate('queue_date', today())->where('status', 'done');
            },
            'queues as waiting' => function ($q) {
                $q->whereDate('queue_date', today())->where('status', 'waiting');
            }
        ])->where('is_active', true)->get();

        // Antrian terbaru (8 data)
        $recent = Queue::with(['patient', 'poly', 'doctor.user'])
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'polyToday', 'recent'));
    }
}