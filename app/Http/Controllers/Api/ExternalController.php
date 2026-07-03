<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ExternalController extends Controller
{
    /**
     * Handler utama untuk external API.
     * GET /api/external?action=health_tips
     */
    public function handle(Request $request)
    {
        $action = $request->action;

        switch ($action) {
            case 'health_tips':
                return $this->healthTips();
            case 'covid_stats':
                return $this->covidStats();
            case 'holiday':
                return $this->holiday($request);
            case 'bpjs_info':
                return $this->bpjsInfo();
            case 'drug_info':
                return $this->drugInfo($request);
            default:
                return response()->json([
                    'success' => true,
                    'app' => 'Klinik Gen-Z External API',
                    'actions' => [
                        'health_tips' => 'GET /api/external?action=health_tips',
                        'covid_stats' => 'GET /api/external?action=covid_stats',
                        'holiday' => 'GET /api/external?action=holiday&year=2024',
                        'bpjs_info' => 'GET /api/external?action=bpjs_info',
                        'drug_info' => 'GET /api/external?action=drug_info&q=paracetamol',
                    ]
                ]);
        }
    }

    // 1. Health Tips (local)
    private function healthTips()
    {
        $tips = [
            ['id' => 1, 'category' => 'Umum', 'icon' => '💧', 'title' => 'Minum Air Cukup', 'content' => 'Minum minimal 8 gelas air per hari untuk menjaga kesehatan tubuh dan kulit.'],
            ['id' => 2, 'category' => 'Umum', 'icon' => '🏃', 'title' => 'Olahraga Rutin', 'content' => '30 menit olahraga ringan setiap hari dapat mengurangi risiko penyakit jantung hingga 35%.'],
            ['id' => 3, 'category' => 'Gizi', 'icon' => '🥗', 'title' => 'Konsumsi Sayur & Buah', 'content' => 'Konsumsi 5 porsi sayur dan buah setiap hari untuk mendapatkan vitamin dan mineral yang cukup.'],
            ['id' => 4, 'category' => 'Mental', 'icon' => '😴', 'title' => 'Tidur Berkualitas', 'content' => 'Tidur 7-9 jam setiap malam membantu pemulihan tubuh dan meningkatkan daya ingat.'],
            ['id' => 5, 'category' => 'Gigi', 'icon' => '🦷', 'title' => 'Sikat Gigi 2x Sehari', 'content' => 'Sikat gigi pagi dan malam mencegah gigi berlubang dan bau mulut.'],
            ['id' => 6, 'category' => 'Mata', 'icon' => '👁', 'title' => 'Istirahat Mata 20-20-20', 'content' => 'Tiap 20 menit, alihkan pandangan 20 detik ke objek sejauh 20 kaki.'],
            ['id' => 7, 'category' => 'Jantung', 'icon' => '❤️', 'title' => 'Kurangi Garam & Lemak', 'content' => 'Konsumsi garam berlebih meningkatkan tekanan darah. Batasi <5g garam.'],
            ['id' => 8, 'category' => 'Anak', 'icon' => '🧒', 'title' => 'Imunisasi Lengkap', 'content' => 'Pastikan anak mendapat imunisasi dasar lengkap sesuai jadwal.'],
            ['id' => 9, 'category' => 'Umum', 'icon' => '🚭', 'title' => 'Hindari Rokok & Alkohol', 'content' => 'Rokok menjadi penyebab 6 juta kematian per tahun.'],
            ['id' => 10, 'category' => 'Mental', 'icon' => '🧘', 'title' => 'Kelola Stres', 'content' => 'Meditasi 10 menit sehari terbukti mengurangi stres, kecemasan, dan tekanan darah.'],
        ];

        shuffle($tips);

        return response()->json([
            'success' => true,
            'source' => 'Klinik Gen-Z Health Database',
            'tips' => $tips,
            'count' => count($tips),
        ]);
    }

    // 2. COVID Stats (disease.sh)
    private function covidStats()
    {
        try {
            $response = Http::timeout(8)->get('https://disease.sh/v3/covid-19/countries/Indonesia');
            $data = $response->json();

            if (!$data) {
                return $this->covidFallback();
            }

            return response()->json([
                'success' => true,
                'source' => 'disease.sh',
                'data' => [
                    'country' => $data['country'] ?? 'Indonesia',
                    'cases' => $data['cases'] ?? 0,
                    'deaths' => $data['deaths'] ?? 0,
                    'recovered' => $data['recovered'] ?? 0,
                    'active' => $data['active'] ?? 0,
                    'tests' => $data['tests'] ?? 0,
                    'updated' => isset($data['updated']) ? date('d M Y H:i', $data['updated'] / 1000) : '—',
                    'flag' => $data['countryInfo']['flag'] ?? '',
                ]
            ]);
        } catch (\Exception $e) {
            return $this->covidFallback();
        }
    }

    private function covidFallback()
    {
        return response()->json([
            'success' => true,
            'source' => 'Static fallback',
            'data' => [
                'country' => 'Indonesia',
                'cases' => 6812000,
                'deaths' => 161000,
                'recovered' => 6620000,
                'active' => 31000,
                'updated' => 'Data lokal',
            ]
        ]);
    }

    // 3. Holiday (Nager.Date)
    private function holiday(Request $request)
    {
        $year = (int) ($request->year ?? date('Y'));
        $year = max(2020, min($year, 2030));

        try {
            $response = Http::timeout(8)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/ID");
            $data = $response->json();

            if (!$data || !is_array($data)) {
                return $this->holidayFallback($year);
            }

            $holidays = array_map(function ($h) {
                return [
                    'date' => $h['date'],
                    'name' => $h['localName'] ?? $h['name'],
                    'name_en' => $h['name'],
                    'is_national' => $h['global'] ?? false,
                ];
            }, $data);

            return response()->json([
                'success' => true,
                'source' => 'Nager.Date',
                'year' => $year,
                'holidays' => $holidays,
                'count' => count($holidays),
            ]);
        } catch (\Exception $e) {
            return $this->holidayFallback($year);
        }
    }

    private function holidayFallback($year)
    {
        return response()->json([
            'success' => true,
            'source' => 'Local fallback',
            'year' => $year,
            'holidays' => [
                ['date' => "{$year}-01-01", 'name' => 'Tahun Baru Masehi'],
                ['date' => "{$year}-08-17", 'name' => 'Hari Kemerdekaan Indonesia'],
                ['date' => "{$year}-12-25", 'name' => 'Hari Natal'],
            ]
        ]);
    }

    // 4. BPJS Info (static)
    private function bpjsInfo()
    {
        return response()->json([
            'success' => true,
            'source' => 'BPJS Kesehatan (static)',
            'info' => [
                'hotline' => '1500-400',
                'website' => 'https://bpjs-kesehatan.go.id',
                'mobile_app' => 'Mobile JKN',
                'iuran' => [
                    ['kelas' => 'III', 'nominal' => 'Rp 42.000/bulan', 'keterangan' => 'Perawatan kelas III'],
                    ['kelas' => 'II', 'nominal' => 'Rp 100.000/bulan', 'keterangan' => 'Perawatan kelas II'],
                    ['kelas' => 'I', 'nominal' => 'Rp 150.000/bulan', 'keterangan' => 'Perawatan kelas I'],
                ],
                'requirements' => ['KTP', 'KK', 'Foto 3x4', 'Formulir pendaftaran', 'Rekening tabungan'],
                'layanan_faskes_1' => [
                    'Konsultasi dokter umum',
                    'Pemeriksaan fisik',
                    'Tindakan medis non-spesialistik',
                    'Pemberian obat sesuai formularium',
                    'Laboratorium dasar',
                    'Rujukan ke FKRTL jika perlu',
                ],
            ]
        ]);
    }

    // 5. Drug Info (OpenFDA)
    private function drugInfo(Request $request)
    {
        $query = trim($request->q ?? '');

        if (!$query) {
            return response()->json(['success' => false, 'error' => 'Query required'], 400);
        }

        try {
            $response = Http::timeout(8)->get("https://api.fda.gov/drug/label.json", [
                'search' => "openfda.brand_name:{$query}",
                'limit' => 3,
            ]);

            $data = $response->json();

            if (empty($data['results'])) {
                return response()->json(['success' => false, 'error' => 'Obat tidak ditemukan', 'data' => []]);
            }

            $results = array_map(function ($d) {
                return [
                    'name' => $d['openfda']['brand_name'][0] ?? 'Unknown',
                    'generic' => $d['openfda']['generic_name'][0] ?? '',
                    'manufacturer' => $d['openfda']['manufacturer_name'][0] ?? '',
                    'purpose' => $d['purpose'][0] ?? '',
                    'warnings' => isset($d['warnings']) ? substr($d['warnings'][0], 0, 300) . '...' : '',
                ];
            }, $data['results']);

            return response()->json([
                'success' => true,
                'source' => 'OpenFDA',
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Gagal mengambil data obat'], 500);
        }
    }
}