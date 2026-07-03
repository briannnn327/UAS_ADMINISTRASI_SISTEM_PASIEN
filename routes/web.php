<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DoctorController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\QueueController as AdminQueueController;
use App\Http\Controllers\Admin\ReportController;

use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\ScheduleController as DoctorScheduleController;
use App\Http\Controllers\Doctor\PatientController;
use App\Http\Controllers\Doctor\HistoryController;

use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\Patient\PolyController;
use App\Http\Controllers\Patient\QueueController as PatientQueueController;
use App\Http\Controllers\Patient\SurveyController as PatientSurveyController;

use App\Http\Controllers\QueueHistoryController;
use App\Http\Controllers\SurveyFormController;
use App\Http\Controllers\SurveyResultController;

use Illuminate\Support\Facades\Route;

// ============================
// HALAMAN PUBLIK (LANDING)
// ============================
Route::get('/', function () {
    return view('landing');
})->name('landing');

// ============================
// RUTE AUTENTIKASI (Login, Register, Forgot Password, Reset Password)
// Semua route GET + POST disediakan oleh Breeze di routes/auth.php
// ============================
require __DIR__.'/auth.php';

// ============================
// GROUP MIDDLEWARE AUTH + ROLE
// ============================
Route::middleware(['auth'])->group(function () {

    // ---------- ADMIN ----------
    Route::prefix('admin')->middleware(['role:admin'])->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Manajemen User
        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggleActive'])->name('users.toggle');

        // Manajemen Dokter
        Route::resource('doctors', DoctorController::class)->except(['show']);
        Route::patch('doctors/{doctor}/toggle', [DoctorController::class, 'toggleAvailability'])->name('doctors.toggle');

        // Manajemen Jadwal
        Route::resource('schedules', ScheduleController::class)->except(['show']);
        Route::patch('schedules/{schedule}/toggle', [ScheduleController::class, 'toggleAvailability'])->name('schedules.toggle');

        // Manajemen Antrian
        Route::get('/queues', [AdminQueueController::class, 'index'])->name('queues.index');
        Route::post('/queues/call', [AdminQueueController::class, 'call'])->name('queues.call');
        Route::put('/queues/status', [AdminQueueController::class, 'updateStatus'])->name('queues.updateStatus');

        // Laporan & Survei
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/send-thanks', [ReportController::class, 'sendThanks'])->name('reports.sendThanks');
    });

    // ---------- DOKTER ----------
    Route::prefix('doctor')->middleware(['role:doctor'])->name('doctor.')->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');

        Route::get('/schedule', [DoctorScheduleController::class, 'index'])->name('schedule');
        Route::patch('/schedule/toggle', [DoctorScheduleController::class, 'toggle'])->name('schedule.toggle');

        Route::get('/patients', [PatientController::class, 'index'])->name('patients.index');
        Route::put('/patients/status', [PatientController::class, 'updateStatus'])->name('patients.updateStatus');

        Route::get('/history', [HistoryController::class, 'index'])->name('history');
    });

    // ---------- PASIEN ----------
    Route::prefix('patient')->middleware(['role:patient'])->name('patient.')->group(function () {
        Route::get('/dashboard', [PatientDashboardController::class, 'index'])->name('dashboard');

        Route::get('/poli-info', [PolyController::class, 'index'])->name('poli-info');
        Route::get('/poli-info/{poly}', [PolyController::class, 'show'])->name('poli-info.show');

        Route::get('/register-queue', [PatientQueueController::class, 'create'])->name('register-queue');
        Route::post('/register-queue', [PatientQueueController::class, 'store'])->name('register-queue.store');
        // ============ NOTIFIKASI (AJAX) ============
        Route::get('/notification/list', [App\Http\Controllers\Api\NotificationController::class, 'list']);
        Route::post('/notification/mark-read', [App\Http\Controllers\Api\NotificationController::class, 'markRead']);
        Route::post('/notification/read-one', [App\Http\Controllers\Api\NotificationController::class, 'readOne']);

        Route::get('/survey-chart', [PatientSurveyController::class, 'chart'])->name('survey-chart');
    });

    // Registrasi Antrian (AJAX)
    Route::post('/queue-register', [App\Http\Controllers\Api\QueueRegisterController::class, 'store']);

    // ---------- QUEUE HISTORY (semua role) ----------
    Route::get('/queue/history', [QueueHistoryController::class, 'index'])->name('queue.history');

    // ---------- SURVEY ----------
    Route::get('/survey/form/{queue}', [SurveyFormController::class, 'create'])->name('survey.form');
    Route::post('/survey/store', [SurveyFormController::class, 'store'])->name('survey.store');
    Route::get('/survey/results', [SurveyResultController::class, 'index'])->name('survey.results');

    // ---------- LOGOUT (Custom, Breeze juga menyediakan) ----------
    Route::post('/logout', function () {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});