@extends('layouts.app')

@section('title', 'Grafik Survei')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-patient')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Grafik Survei Kepuasan</h1>
        <p class="text-gray-400 text-sm mb-6">Statistik kepuasan pasien klinik secara keseluruhan</p>

        @php
            $user = auth()->user();

            // Global stats
            $global = \App\Models\Survey::select(
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(doctor_rating), 2) as avg_doc'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(service_rating), 2) as avg_svc'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(facility_rating), 2) as avg_fac'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 2) as avg_all'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
            )->first();

            // Trend 6 bulan (perbaiki dengan MIN)
            $trend = \App\Models\Survey::select(
                \Illuminate\Support\Facades\DB::raw("DATE_FORMAT(MIN(created_at), '%b %Y') as mon"),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 2) as avg'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt')
            )
                ->groupBy(\Illuminate\Support\Facades\DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy(\Illuminate\Support\Facades\DB::raw('MIN(created_at)'), 'desc')
                ->limit(6)
                ->get()
                ->reverse()
                ->values();

            // Distribution
            $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $distData = \App\Models\Survey::select('overall_rating', \Illuminate\Support\Facades\DB::raw('COUNT(*) as n'))
                ->groupBy('overall_rating')
                ->pluck('n', 'overall_rating')
                ->toArray();
            foreach ($distData as $rating => $count) {
                $dist[(int) $rating] = $count;
            }

            // Per poly
            $perPoli = \App\Models\Poly::withCount(['queues as survey_count' => function ($q) {
                $q->join('surveys', 'queues.id', '=', 'surveys.queue_id');
            }])
                ->withAvg(['queues as avg_rating' => function ($q) {
                    $q->join('surveys', 'queues.id', '=', 'surveys.queue_id')
                        ->select(\Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 2)'));
                }], 'overall_rating')
                ->get();

            // My surveys
            $mySurveys = \App\Models\Survey::with(['queue.poly'])
                ->where('patient_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Pending surveys
            $pendingSurveys = \App\Models\Queue::with('poly')
                ->where('patient_id', $user->id)
                ->where('status', 'done')
                ->whereDoesntHave('survey')
                ->orderBy('queue_date', 'desc')
                ->get();

            // === Siapkan data untuk JavaScript ===
            $trendLabels = $trend->pluck('mon');
            $trendData   = $trend->pluck('avg');
            $distValues  = array_values($dist);
            $radarData   = [
                (float) ($global->avg_doc ?? 0),
                (float) ($global->avg_svc ?? 0),
                (float) ($global->avg_fac ?? 0),
                (float) ($global->avg_all ?? 0),
                (float) ($global->avg_doc ?? 0) // kembali ke awal untuk menutup radar
            ];
        @endphp

        <!-- Pending survey alerts -->
        @foreach($pendingSurveys as $pq)
            <div class="bg-amber-50 border border-amber-300 rounded-2xl p-4 mb-4 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-400 flex items-center justify-center text-white flex-shrink-0">
                    <i class="fa-solid fa-star"></i>
                </div>
                <div class="flex-1">
                    <p class="font-semibold text-amber-800 text-sm">Survei belum diisi — {{ $pq->poly->name }}</p>
                    <p class="text-xs text-amber-600">{{ date('d M Y', strtotime($pq->queue_date)) }}</p>
                </div>
                <a href="{{ route('survey.form', $pq->id) }}"
                   class="flex-shrink-0 bg-amber-400 hover:bg-amber-500 text-white px-4 py-2 rounded-xl text-xs font-bold transition">
                    Isi Sekarang <i class="fa-solid fa-arrow-right ml-1"></i>
                </a>
            </div>
        @endforeach

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['Dokter', $global->avg_doc, 'fa-user-doctor', 'from-blue-500 to-blue-700'],
                ['Layanan', $global->avg_svc, 'fa-bell', 'from-cyan-500 to-teal-600'],
                ['Fasilitas', $global->avg_fac, 'fa-building', 'from-green-500 to-emerald-600'],
                ['Overall', $global->avg_all, 'fa-star', 'from-yellow-400 to-orange-500'],
            ] as [$lbl, $val, $ico, $grad])
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white mb-3">
                    <i class="fa-solid {{ $ico }}"></i>
                </div>
                <div class="text-3xl font-extrabold text-gray-900 leading-none">
                    {{ $val ?? '—' }}<span class="text-base text-gray-400">/5</span>
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ $lbl }}</div>
                <div class="flex gap-0.5 mt-2">
                    @for($i=1; $i<=5; $i++)
                        <i class="fa-{{ $i <= round((float)($val ?? 0)) ? 'solid' : 'regular' }} fa-star text-yellow-400 text-xs"></i>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>

        <!-- Charts -->
        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">Tren Kepuasan 6 Bulan Terakhir</h3>
                    <span class="text-xs text-gray-400">{{ $global->total }} total responden</span>
                </div>
                <canvas id="trendChart" height="100"></canvas>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Distribusi Rating</h3>
                <canvas id="distChart" height="180"></canvas>
                <div class="flex flex-wrap gap-2 mt-3 justify-center">
                    @foreach([1=>'#ef4444',2=>'#f97316',3=>'#eab308',4=>'#22c55e',5=>'#1565C0'] as $r => $c)
                        <div class="flex items-center gap-1 text-xs">
                            <span class="w-3 h-3 rounded-full inline-block" style="background:{{ $c }}"></span>
                            {{ $r }}★ ({{ $dist[$r] }})
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Per-poli bars -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
            <h3 class="font-bold text-gray-800 mb-4">Kepuasan Per Poliklinik</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($perPoli as $pp)
                    @php $pct = $pp->avg_rating ? round($pp->avg_rating / 5 * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-semibold" style="color:{{ $pp->color }}">{{ $pp->name }}</span>
                            <span class="text-gray-400">{{ $pp->avg_rating ?? '—' }}/5 · {{ $pp->survey_count }} resp.</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-3">
                            <div class="h-3 rounded-full transition-all"
                                 style="width:{{ $pct }}%;background:{{ $pp->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Radar + My Surveys -->
        <div class="grid lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Radar Kepuasan Global</h3>
                <canvas id="radarChart" height="180"></canvas>
            </div>

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800">Survei Saya ({{ $mySurveys->count() }})</h3>
                    @if($pendingSurveys->count())
                        <a href="{{ route('survey.form', $pendingSurveys->first()->id) }}"
                           class="text-xs bg-amber-400 text-white px-3 py-1 rounded-lg font-semibold hover:bg-amber-500">
                            + Isi Survei
                        </a>
                    @endif
                </div>
                @if($mySurveys->isEmpty())
                    <div class="py-10 text-center text-gray-400 text-sm">
                        <i class="fa-solid fa-star text-2xl mb-2 block text-gray-200"></i>
                        Belum ada survei yang diisi.
                    </div>
                @else
                    <div class="divide-y divide-gray-50 max-h-64 overflow-y-auto">
                        @foreach($mySurveys as $sv)
                            <div class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                     style="background:{{ $sv->queue->poly->color ?? '#1565C0' }}">
                                    {{ $sv->overall_rating }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-sm">{{ $sv->queue->poly->name ?? 'Poli' }}</p>
                                    <p class="text-xs text-gray-400">{{ date('d M Y', strtotime($sv->created_at)) }}</p>
                                </div>
                                <div class="flex gap-0.5">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="fa-{{ $i <= $sv->overall_rating ? 'solid' : 'regular' }} fa-star text-yellow-400 text-xs"></i>
                                    @endfor
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>

@section('scripts')
@php
    // Pastikan semua data siap untuk JSON
    $chartData = [
        'trendLabels' => $trendLabels,
        'trendData'   => $trendData,
        'distData'    => $distValues,
        'radarData'   => $radarData,
    ];
@endphp
<script>
    // Data dari server
    const chartData = @json($chartData);

    // Trend chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: chartData.trendLabels,
            datasets: [{
                label: 'Rata-rata Rating',
                data: chartData.trendData,
                borderColor: '#1565C0',
                backgroundColor: 'rgba(21,101,192,.1)',
                tension: .4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#1565C0'
            }]
        },
        options: {
            scales: { y: { min: 1, max: 5, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });

    // Distribution chart (doughnut)
    new Chart(document.getElementById('distChart'), {
        type: 'doughnut',
        data: {
            labels: ['1★', '2★', '3★', '4★', '5★'],
            datasets: [{
                data: chartData.distData,
                backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#1565C0'],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            cutout: '65%'
        }
    });

    // Radar chart
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: ['Dokter', 'Layanan', 'Fasilitas', 'Overall'],
            datasets: [{
                label: 'Kepuasan',
                data: [
                    chartData.radarData[0],
                    chartData.radarData[1],
                    chartData.radarData[2],
                    chartData.radarData[3]
                ],
                backgroundColor: 'rgba(21,101,192,.15)',
                borderColor: '#1565C0',
                pointBackgroundColor: '#1565C0'
            }]
        },
        options: {
            scales: { r: { min: 0, max: 5, ticks: { stepSize: 1 } } },
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection
@endsection