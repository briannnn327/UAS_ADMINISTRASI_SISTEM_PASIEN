<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    /**
     * Buat notifikasi baru.
     */
    public function create(int $userId, string $type, string $title, string $message, ?int $queueId = null): int
    {
        $notification = Notification::create([
            'user_id'  => $userId,
            'queue_id' => $queueId,
            'type'     => $type,
            'title'    => $title,
            'message'  => $message,
            'is_read'  => false,
        ]);

        return $notification->id;
    }

    /**
     * Hitung notifikasi belum dibaca.
     */
    public function unreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca.
     */
    public function markAllRead(int $userId): void
    {
        Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca.
     */
    public function markRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        $notification->update(['is_read' => true]);
        return true;
    }
}