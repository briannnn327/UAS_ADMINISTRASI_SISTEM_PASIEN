<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    'queue_notify_minutes' => env('QUEUE_NOTIFY_MINUTES', 10),
    'queue_late_minutes' => env('QUEUE_LATE_MINUTES', 15),
    'queue_busy_threshold' => env('QUEUE_BUSY_THRESHOLD', 10),

    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    */
    'wa_endpoint' => env('WA_ENDPOINT', 'https://api.fonnte.com/send'),
    'wa_token' => env('WA_API_TOKEN', ''),
    'wa_cron_key' => env('WA_CRON_KEY', 'klinikgenz_cron_2024'),

    /*
    |--------------------------------------------------------------------------
    | Indonesian Day Names
    |--------------------------------------------------------------------------
    */
    'days_id' => [
        0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
        3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Status Meta
    |--------------------------------------------------------------------------
    */
    'queue_status_meta' => [
        'waiting'     => ['label' => 'Menunggu', 'tw' => 'bg-yellow-100 text-yellow-800'],
        'called'      => ['label' => 'Dipanggil', 'tw' => 'bg-blue-100 text-blue-800'],
        'in_progress' => ['label' => 'Dalam Pemeriksaan', 'tw' => 'bg-indigo-100 text-indigo-800'],
        'done'        => ['label' => 'Selesai', 'tw' => 'bg-green-100 text-green-800'],
        'late'        => ['label' => 'Terlambat', 'tw' => 'bg-red-100 text-red-800'],
        'cancelled'   => ['label' => 'Dibatalkan', 'tw' => 'bg-gray-100 text-gray-600'],
        'rescheduled' => ['label' => 'Dijadwalkan Ulang', 'tw' => 'bg-purple-100 text-purple-800'],
    ],
];