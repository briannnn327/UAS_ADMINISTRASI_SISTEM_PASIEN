<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Queue;
use App\Services\QueueService;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueueController extends Controller
{
    protected $queueService;
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(QueueService $queueService, NotificationService $notificationService, WhatsAppService $whatsAppService)
    {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * GET /api/queue/status?id=1
     */
    public function status(Request $request)
    {
        $id = (int) $request->id;
        if (!$id) {
            return response()->json(['success' => false, 'error' => 'ID required'], 400);
        }

        $queue = Queue::with(['poly', 'doctor.user'])->find($id);
        if (!$queue) {
            return response()->json(['success' => false, 'error' => 'Queue not found'], 404);
        }

        $meta = $queue->status_meta;
        $position = $queue->position;

        return response()->json([
            'success' => true,
            'status' => $queue->status,
            'status_label' => $meta['label'],
            'status_tw' => $meta['tw'],
            'position' => $position,
            'estimated' => $queue->estimated_time ? date('H:i', strtotime($queue->estimated_time)) : null,
            'survey_url' => $queue->status === 'done' ? route('survey.form', $queue->id) : null,
        ]);
    }

    /**
     * GET /api/queue/list?poly_id=1&date=2024-01-01
     */
    public function list(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $polyId = (int) $request->poly_id;
        $date = $request->date ?? date('Y-m-d');

        $query = Queue::with(['patient', 'poly'])
            ->whereDate('queue_date', $date);

        if ($polyId) {
            $query->where('poly_id', $polyId);
        }

        $queues = $query->orderBy('queue_number')->get();

        return response()->json(['success' => true, 'queues' => $queues]);
    }

    /**
     * POST /api/queue/update-status
     */
    public function updateStatus(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        $validated = $request->validate([
            'queue_id' => 'required|exists:queues,id',
            'status' => 'required|in:waiting,called,in_progress,done,late,cancelled,rescheduled',
        ]);

        $queue = Queue::find($validated['queue_id']);

        if ($user->role === 'patient') {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        if ($user->role === 'doctor') {
            $doctor = $user->doctor;
            if (!$doctor || $queue->doctor_id !== $doctor->id) {
                return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
            }
        }

        $newStatus = $validated['status'];

        if ($newStatus === 'late') {
            $this->queueService->handleLate($queue);
            return response()->json(['success' => true, 'action' => 'late_handled']);
        }

        $queue->update(['status' => $newStatus]);

        if ($newStatus === 'done') {
            $this->queueService->sendSurveyNotification($queue);
        }

        return response()->json(['success' => true, 'status' => $newStatus]);
    }

    /**
     * POST /api/queue/cancel
     */
    public function cancel(Request $request)
    {
        if (!Auth::check() || Auth::user()->role !== 'patient') {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $queueId = (int) $request->queue_id;
        $user = Auth::user();

        $queue = Queue::where('id', $queueId)
            ->where('patient_id', $user->id)
            ->where('status', 'waiting')
            ->first();

        if (!$queue) {
            return response()->json(['success' => false, 'error' => 'Cannot cancel'], 400);
        }

        $queue->update(['status' => 'cancelled']);

        return response()->json(['success' => true]);
    }
}