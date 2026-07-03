<?php

use App\Http\Controllers\Api\ExternalController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\QueueController as ApiQueueController;
use App\Http\Controllers\Api\QueueRegisterController;
use App\Http\Controllers\Api\ScheduleController as ApiScheduleController;
use App\Http\Controllers\Api\SurveyController as ApiSurveyController;
use App\Http\Controllers\Api\WhatsAppController;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ENDPOINTS (tanpa auth)
// ==========================================
Route::get('/external', [ExternalController::class, 'handle']);
Route::get('/queue/status', [ApiQueueController::class, 'status']);
Route::get('/schedule/by-doctor', [ApiScheduleController::class, 'byDoctor']);
Route::get('/schedule/by-poly', [ApiScheduleController::class, 'byPoly']);
Route::get('/schedule/today', [ApiScheduleController::class, 'today']);
Route::get('/survey/stats', [ApiSurveyController::class, 'stats']);
Route::get('/survey/trend', [ApiSurveyController::class, 'trend']);

// ==========================================
// AUTH REQUIRED ENDPOINTS (SESSION-BASED)
// ==========================================
Route::middleware('auth')->group(function () {
    // Notifikasi
 // Route::get('/notification/list', [NotificationController::class, 'list']);
 // Route::post('/notification/mark-read', [NotificationController::class, 'markRead']);
 // Route::post('/notification/read-one', [NotificationController::class, 'readOne']);
 // Route::post('/notification/create', [NotificationController::class, 'create']);

    // Antrian
    Route::get('/queue/list', [ApiQueueController::class, 'list']);
    Route::post('/queue/update-status', [ApiQueueController::class, 'updateStatus']);
    Route::post('/queue/cancel', [ApiQueueController::class, 'cancel']);

    // Jadwal (toggle)
    Route::post('/schedule/toggle', [ApiScheduleController::class, 'toggle']);

    // Survei (list admin)
    Route::get('/survey/list', [ApiSurveyController::class, 'list']);

    // WhatsApp cron (bisa diakses admin)
    Route::get('/whatsapp/cron', [WhatsAppController::class, 'cron']);
});

