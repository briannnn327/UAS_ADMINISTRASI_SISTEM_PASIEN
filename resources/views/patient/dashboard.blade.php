@extends('layouts.app')

@section('title', 'Dashboard Pasien')

@section('content')
<div class="flex flex-1">
    <!-- Sidebar Pasien -->
    @include('partials.sidebar-patient')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Dashboard Pasien</h1>
        <p class="text-gray-400 text-sm mb-5">Selamat datang, {{ auth()->user()->username }}! — {{ date('l, d F Y') }}</p>

        @php
            $user = auth()->user();
            $activeQueue = \App\Models\Queue::with(['poly', 'doctor.user'])
                ->where('patient_id', $user->id)
                ->whereDate('queue_date', today())
                ->whereNotIn('status', ['done', 'cancelled'])
                ->first();

            $totalQueues = \App\Models\Queue::where('patient_id', $user->id)->count();
            $totalSurveys = \App\Models\Survey::where('patient_id', $user->id)->count();

            $pendingSurvey = \App\Models\Queue::where('patient_id', $user->id)
                ->where('status', 'done')
                ->whereDoesntHave('survey')
                ->first();

            $recentQueues = \App\Models\Queue::with('poly')
                ->where('patient_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get();

            // ===== AMBIL DATA BPS (caching 1 jam) =====
            $bpsData = \Illuminate\Support\Facades\Cache::remember('bps_data', 3600, function () {
                try {
                    $url = "https://webapi.bps.go.id/v1/api/interoperabilitas/datasource/simdasi/id/25/tahun/2017/id_tabel/TEptbDV0QlRORVl6cjl0THhMbk02Zz09/wilayah/0000000/key/e34d50c3e2e4773ebe4c8162f7a76057";
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    if (!$response) return null;
                    $data = json_decode($response, true);
                    return $data['data'] ?? $data;
                } catch (\Exception $e) {
                    return null;
                }
            });

            // Proses data BPS
            $bpsList = [];
            if ($bpsData && is_array($bpsData) && count($bpsData) > 0) {
                // Coba beberapa format data yang mungkin
                if (isset($bpsData[0]) && is_array($bpsData[0])) {
                    $bpsList = $bpsData;
                } elseif (isset($bpsData['data']) && is_array($bpsData['data'])) {
                    $bpsList = $bpsData['data'];
                } else {
                    $bpsList = $bpsData;
                }
            }

            // Pastikan hanya ambil 12 data pertama (sesuai screenshot)
            $bpsList = array_slice($bpsList, 0, 12);
        @endphp

        <!-- Pending Survey Alert -->
        @if($pendingSurvey)
        <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 mb-5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-400 flex items-center justify-center text-white flex-shrink-0">
                <i class="fa-solid fa-star"></i>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-amber-800">Mohon isi survei kepuasan Anda!</div>
                <div class="text-xs text-amber-600">Pemeriksaan terakhir Anda telah selesai. Penilaian Anda sangat berarti bagi kami.</div>
            </div>
            <a href="{{ route('survey.form', $pendingSurvey->id) }}"
               class="flex-shrink-0 bg-amber-400 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-500 transition">
                Isi Survei <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif

        <!-- Active Queue Card -->
        @if($activeQueue)
            @php $sm = $activeQueue->status_meta; @endphp
            <div class="rounded-2xl p-5 mb-6 text-white" style="background:linear-gradient(135deg, {{ $activeQueue->poly->color }}, {{ $activeQueue->poly->color }}99)">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <div class="text-xs font-semibold opacity-75 mb-1">Antrian Aktif Hari Ini</div>
                        <div class="text-4xl font-extrabold mb-1">#{{ $activeQueue->queue_number }}</div>
                        <div class="text-sm opacity-90">{{ $activeQueue->poly->name }} • dr. {{ $activeQueue->doctor->user->username }}</div>
                        <div class="text-xs opacity-75 mt-1">Est. jam {{ $activeQueue->estimated_time ? date('H:i', strtotime($activeQueue->estimated_time)) : '—' }}</div>
                    </div>
                    <div class="text-right">
                        <span id="q-status-badge" class="inline-block px-3 py-1 rounded-full text-sm font-semibold {{ $sm['tw'] }}">
                            {{ $sm['label'] }}
                        </span>
                        <div class="text-xs opacity-70 mt-2">Real-time update otomatis</div>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-dashed border-gray-200 p-6 mb-6 text-center text-gray-400">
                <i class="fa-solid fa-ticket text-3xl mb-2 block"></i>
                <p class="text-sm">Belum ada antrian aktif hari ini.</p>
                <a href="{{ route('patient.register-queue') }}" class="inline-flex items-center gap-2 mt-3 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                    <i class="fa-solid fa-plus"></i>Ambil Antrian
                </a>
            </div>
        @endif

        <!-- 3 Main Menu Cards -->
        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <a href="{{ route('patient.poli-info') }}" class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition p-5 flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-hospital"></i>
                </div>
                <div class="font-bold text-gray-800 mb-1">Jumlah Pasien & Jadwal Dokter</div>
                <p class="text-xs text-gray-400 flex-1">Lihat info poli, jadwal dokter aktif, dan jumlah antrian hari ini.</p>
                <div class="text-primary text-xs font-semibold mt-3">Buka <i class="fa-solid fa-arrow-right ml-1"></i></div>
            </a>
            <a href="{{ route('patient.register-queue') }}" class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition p-5 flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 flex items-center justify-center text-white text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-ticket"></i>
                </div>
                <div class="font-bold text-gray-800 mb-1">Pendaftaran Antrian</div>
                <p class="text-xs text-gray-400 flex-1">Daftar antrian online di poli dan dokter pilihan Anda.</p>
                <div class="text-primary text-xs font-semibold mt-3">Buka <i class="fa-solid fa-arrow-right ml-1"></i></div>
            </a>
            <a href="{{ route('patient.survey-chart') }}" class="group bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition p-5 flex flex-col">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center text-white text-xl mb-4 group-hover:scale-110 transition">
                    <i class="fa-solid fa-chart-bar"></i>
                </div>
                <div class="font-bold text-gray-800 mb-1">Grafik Hasil Survei</div>
                <p class="text-xs text-gray-400 flex-1">Lihat statistik kepuasan pasien klinik secara keseluruhan.</p>
                <div class="text-primary text-xs font-semibold mt-3">Buka <i class="fa-solid fa-arrow-right ml-1"></i></div>
            </a>
        </div>

        <!-- Stats + Recent -->
        <div class="grid sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                <div class="text-3xl font-extrabold text-primary">{{ $totalQueues }}</div>
                <div class="text-xs text-gray-400 mt-1">Total Kunjungan</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                <div class="text-3xl font-extrabold text-teal-600">{{ $totalSurveys }}</div>
                <div class="text-xs text-gray-400 mt-1">Survei Diisi</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center">
                <div class="text-3xl font-extrabold text-violet-600">{{ $activeQueue ? 1 : 0 }}</div>
                <div class="text-xs text-gray-400 mt-1">Antrian Aktif</div>
            </div>
        </div>

        <!-- Recent queues -->
        @if($recentQueues->count())
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Antrian Terakhir</h3>
                <a href="{{ route('queue.history') }}" class="text-primary text-xs font-medium">Lihat semua →</a>
            </div>
            <div class="divide-y divide-gray-50">
                @foreach($recentQueues as $q)
                    @php $sm = $q->status_meta; @endphp
                    <div class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50">
                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-white text-xs flex-shrink-0"
                              style="background:{{ $q->poly->color }}">#{{ $q->queue_number }}</span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium text-sm">{{ $q->poly->name }}</div>
                            <div class="text-xs text-gray-400">{{ date('d M Y', strtotime($q->queue_date)) }}</div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

<!-- ===== DATA BPS ===== -->
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mt-6">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-bold text-gray-800">
            <i class="fa-solid fa-chart-simple mr-2 text-primary"></i>
            Persentase Keluhan Kesehatan (BPS 2017)
        </h3>
        <span class="text-xs text-gray-400">Sumber: BPS</span>
    </div>

    <div class="overflow-x-auto max-h-80 overflow-y-auto">
        <table class="w-full text-sm">
            <thead class="bg-blue-50 text-primary sticky top-0">
                <tr>
                    <th class="px-4 py-2 text-left font-semibold text-xs w-12">NO</th>
                    <th class="px-4 py-2 text-left font-semibold text-xs">Provinsi</th>
                    <th class="px-4 py-2 text-right font-semibold text-xs w-32">Persentase Keluhan (%)</th>
                </tr>
            </thead>
            <tbody id="bps-data-body">
                <tr>
                    <td colspan="3" class="text-center text-gray-400 py-4 text-sm">
                        <i class="fa-solid fa-spinner fa-spin mr-2"></i>Memuat data BPS...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
// === FETCH DATA BPS LANGSUNG DARI BROWSER (sama seperti native) ===
async function fetchBPS() {
    const tbody = document.getElementById('bps-data-body');

    // URL API BPS (sama persis dengan yang di native)
    const url = "https://webapi.bps.go.id/v1/api/interoperabilitas/datasource/simdasi/id/25/tahun/2017/id_tabel/TEptbDV0QlRORVl6cjl0THhMbk02Zz09/wilayah/0000000/key/e34d50c3e2e4773ebe4c8162f7a76057";

    try {
        const response = await fetch(url);

        if (!response.ok) {
            throw new Error('Gagal mengambil data. Status: ' + response.status);
        }

        const data = await response.json();

        // Cek struktur data seperti di native: data.data[1].data
        if (data.status === 'OK' && data.data && data.data[1] && data.data[1].data) {
            const provinsiList = data.data[1].data;
            let html = '';

            provinsiList.forEach((item, index) => {
                // Ambil nilai dari variables (key unik seperti lxkwts7rnj)
                const valObj = Object.values(item.variables)[0];
                const nilai = valObj ? valObj.value : '0';

                html += `
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-center text-xs text-gray-500">${index + 1}</td>
                        <td class="px-4 py-2 text-xs font-medium text-gray-700">${item.label}</td>
                        <td class="px-4 py-2 text-right text-xs font-semibold text-primary">${nilai}%</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        } else {
            throw new Error('Format data tidak dikenali atau data kosong.');
        }

    } catch (error) {
        console.error('BPS Error:', error);
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center text-red-500 py-4 text-sm">
                    <i class="fa-solid fa-circle-exclamation mr-2"></i>
                    Gagal memuat data BPS: ${error.message}
                </td>
            </tr>
        `;
    }
}

// Jalankan saat halaman selesai dimuat
document.addEventListener('DOMContentLoaded', fetchBPS);
</script>
@endpush

    </main>
</div>

@if($activeQueue)
    @section('scripts')
    <script>
        startQueuePoll({{ $activeQueue->id }});
    </script>
    @endsection
@endif
@endsection