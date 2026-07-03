@extends('layouts.app')

@section('title', 'Laporan & Survei')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Laporan & Survei Kepuasan</h1>
        <p class="text-gray-400 text-sm mb-5">Analitik kepuasan pasien dan riwayat survei</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        @php
            $avg = \App\Models\Survey::select(
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(doctor_rating), 2) as avg_doc'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(service_rating), 2) as avg_svc'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(facility_rating), 2) as avg_fac'),
                \Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 2) as avg_all'),
                \Illuminate\Support\Facades\DB::raw('COUNT(*) as total')
            )->first();

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

            $dist = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
            $distData = \App\Models\Survey::select('overall_rating', \Illuminate\Support\Facades\DB::raw('COUNT(*) as n'))
                ->groupBy('overall_rating')
                ->pluck('n', 'overall_rating')
                ->toArray();
            foreach ($distData as $rating => $count) {
                $dist[(int) $rating] = $count;
            }

            $perPoli = \App\Models\Poly::withCount(['queues as survey_count' => function ($q) {
                $q->join('surveys', 'queues.id', '=', 'surveys.queue_id');
            }])
                ->withAvg(['queues as avg_rating' => function ($q) {
                    $q->join('surveys', 'queues.id', '=', 'surveys.queue_id')
                        ->select(\Illuminate\Support\Facades\DB::raw('ROUND(AVG(overall_rating), 2)'));
                }], 'overall_rating')
                ->get();

            $surveys = \App\Models\Survey::with(['patient', 'queue.poly'])
                ->orderBy('created_at', 'desc')
                ->limit(25)
                ->get();
        @endphp

        <!-- Summary cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['Kepuasan Dokter', $avg->avg_doc, 'fa-user-doctor', 'from-blue-500 to-blue-600'],
                ['Kepuasan Layanan', $avg->avg_svc, 'fa-concierge-bell', 'from-cyan-500 to-teal-500'],
                ['Kepuasan Fasilitas', $avg->avg_fac, 'fa-building', 'from-green-500 to-emerald-500'],
                ['Rata-rata Overall', $avg->avg_all, 'fa-star', 'from-yellow-400 to-orange-500'],
            ] as [$lbl, $val, $ico, $grad])
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="flex items-center justify-between mb-2">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm">
                        <i class="fa-solid {{ $ico }}"></i>
                    </div>
                    <span class="text-xs text-gray-400">{{ $avg->total }} responden</span>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $val ?? '—' }}<span class="text-base text-gray-400">/5</span></div>
                <div class="text-xs text-gray-500 mt-0.5">{{ $lbl }}</div>
                <div class="flex gap-0.5 mt-1">
                    @for($i=1; $i<=5; $i++)
                        <i class="fa-{{ $i <= round($val ?? 0) ? 'solid' : 'regular' }} fa-star text-yellow-400 text-xs"></i>
                    @endfor
                </div>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-3 gap-6 mb-6">
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tren Kepuasan 6 Bulan Terakhir</h3>
                <canvas id="trendChart" height="100"></canvas>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Distribusi Rating</h3>
                <canvas id="distChart" height="160"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
            <h3 class="font-bold text-gray-800 mb-4">Kepuasan Per Poliklinik</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($perPoli as $pp)
                    @php $pct = $pp->avg_rating ? round($pp->avg_rating / 5 * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium" style="color:{{ $pp->color }}">{{ $pp->name }}</span>
                            <span class="text-gray-400">{{ $pp->avg_rating ?? '—' }}/5 ({{ $pp->survey_count }} resp.)</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full" style="width:{{ $pct }}%;background:{{ $pp->color }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">Riwayat Survei ({{ $surveys->count() }})</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-primary">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                            <th class="px-4 py-3 text-left font-semibold">Poli</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Dokter</th>
                            <th class="px-4 py-3 text-left font-semibold">Layanan</th>
                            <th class="px-4 py-3 text-left font-semibold">Fasilitas</th>
                            <th class="px-4 py-3 text-left font-semibold">Overall</th>
                            <th class="px-4 py-3 text-left font-semibold">WA</th>
                            <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($surveys as $sv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-xs">{{ $sv->patient->username }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $sv->queue->poly->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs">{{ $sv->created_at ? date('d M Y', strtotime($sv->created_at)) : '—' }}</td>
                                <td class="px-4 py-3 text-center">@for($i=1; $i<=$sv->doctor_rating; $i++)⭐ @endfor</td>
                                <td class="px-4 py-3 text-center">@for($i=1; $i<=$sv->service_rating; $i++)⭐ @endfor</td>
                                <td class="px-4 py-3 text-center">@for($i=1; $i<=$sv->facility_rating; $i++)⭐ @endfor</td>
                                <td class="px-4 py-3">
                                    <span class="font-extrabold text-yellow-500">{{ $sv->overall_rating }}</span>
                                    <span class="text-gray-400 text-xs">/5</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($sv->wa_thanks_sent)
                                        <span class="text-xs text-green-600"><i class="fa-brands fa-whatsapp"></i> Terkirim</span>
                                    @else
                                        <span class="text-xs text-gray-400">Belum</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if(!$sv->wa_thanks_sent)
                                        <form method="POST" action="{{ route('admin.reports.sendThanks') }}" class="inline">
                                            @csrf
                                            <input type="hidden" name="survey_id" value="{{ $sv->id }}">
                                            <button type="submit" onclick="return confirm('Kirim WA terima kasih?')"
                                                    class="inline-flex items-center gap-1 bg-green-50 text-green-600 hover:bg-green-100 px-2 py-1 rounded-lg text-xs font-semibold">
                                                <i class="fa-brands fa-whatsapp"></i>Kirim WA
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-300">✓ Terkirim</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @section('scripts')
        <script>
            const tLabels = @json($trend->pluck('mon'));
            const tData = @json($trend->pluck('avg'));
            const dData = @json(array_values($dist));

            new Chart(document.getElementById('trendChart'), {
                type: 'line',
                data: {
                    labels: tLabels,
                    datasets: [{
                        label: 'Avg Rating',
                        data: tData,
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

            new Chart(document.getElementById('distChart'), {
                type: 'bar',
                data: {
                    labels: ['⭐1', '⭐⭐2', '⭐⭐⭐3', '⭐⭐⭐⭐4', '⭐⭐⭐⭐⭐5'],
                    datasets: [{
                        data: dData,
                        backgroundColor: ['#ef4444', '#f97316', '#eab308', '#22c55e', '#1565C0'],
                        borderRadius: 6
                    }]
                },
                options: {
                    plugins: { legend: { display: false } },
                    scales: { y: { ticks: { stepSize: 1 } } }
                }
            });
        </script>
        @endsection
    </main>
</div>
@endsection