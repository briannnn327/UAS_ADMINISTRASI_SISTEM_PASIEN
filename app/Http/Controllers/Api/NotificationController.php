<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;
    protected $whatsAppService;

    public function __construct(NotificationService $notificationService, WhatsAppService $whatsAppService)
    {
        $this->notificationService = $notificationService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * GET /api/notification/list
     */
    public function list()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        $notifications = Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'is_read' => (bool) $n->is_read,
                    'created_at' => $n->created_at->format('d M Y H:i'),
                ];
            });

        $unread = $this->notificationService->unreadCount($userId);

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread' => $unread,
        ]);
    }

    /**
     * POST /api/notification/mark-read
     */
    public function markRead()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $this->notificationService->markAllRead(Auth::id());

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/notification/read-one
     */
    public function readOne(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $id = (int) $request->id;
        if (!$id) {
            return response()->json(['success' => false, 'error' => 'ID required'], 400);
        }

        $result = $this->notificationService->markRead($id, Auth::id());

        return response()->json(['success' => $result]);
    }

    /**
     * POST /api/notification/create (admin/doctor only)
     */
    public function create(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'error' => 'Unauthorized'], 401);
        }

        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'doctor'])) {
            return response()->json(['success' => false, 'error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',
            'type' => 'nullable|string',
            'title' => 'required|string',
            'message' => 'required|string',
            'queue_id' => 'nullable|exists:queues,id',
            'send_wa' => 'nullable|boolean',
        ]);

        $notificationId = $this->notificationService->create(
            $validated['target_user_id'],
            $validated['type'] ?? 'general',
            $validated['title'],
            $validated['message'],
            $validated['queue_id'] ?? null
        );

        // Kirim WA jika diminta
        if (!empty($validated['send_wa'])) {
            $targetUser = \App\Models\User::find($validated['target_user_id']);
            if ($targetUser && $targetUser->phone) {
                $this->whatsAppService->send($targetUser->phone, "{$validated['title']}\n\n{$validated['message']}");
            }
        }

        return response()->json(['success' => true, 'id' => $notificationId]);
    }
}