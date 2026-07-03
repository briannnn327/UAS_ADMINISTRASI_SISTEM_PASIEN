<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected $endpoint;
    protected $token;

    public function __construct()
    {
        $this->endpoint = config('klinik.wa_endpoint', 'https://api.fonnte.com/send');
        $this->token = config('klinik.wa_token');
    }

    /**
     * Kirim pesan WhatsApp via Fonnte API.
     */
    public function send(string $phone, string $message): bool
    {
        if (empty($this->token)) {
            Log::warning('[WA] Token tidak dikonfigurasi, pesan tidak dikirim.');
            return false;
        }

        // Bersihkan nomor telepon
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->token,
            ])->timeout(10)->asForm()->post($this->endpoint, [
                'target' => $phone,
                'message' => $message,
            ]);

            $data = $response->json();

            if ($response->successful() && ($data['status'] ?? '') !== 'false') {
                return true;
            }

            Log::error('[WA] Gagal kirim ke ' . $phone, $data ?? []);
            return false;
        } catch (\Exception $e) {
            Log::error('[WA] Exception: ' . $e->getMessage());
            return false;
        }
    }
}