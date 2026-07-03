@extends('layouts.app')

@section('title', 'Riwayat Antrian')

@section('content')
<div class="flex flex-1">
    @php
        $user = auth()->user();
        $sidebarMap = [
            'admin' => 'partials.sidebar-admin',
            'doctor' => 'partials.sidebar-doctor',
            'patient' => 'partials.sidebar-patient',
        ];
        $sidebar = $sidebarMap[$user->role] ?? 'partials.sidebar-patient';
    @endphp
    @include($sidebar)

    <main class="flex-1 p-6 overflow-x-hidden">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Riwayat Antrian</h1>
                <p class="text-gray-400 text-sm">
                    @if($user->role === 'patient')
                        Status antrian real-time — diperbarui otomatis setiap 15 detik
                    @elseif($user->role === 'doctor')
                        Antrian pasien di jadwal Anda
                    @else
                        Semua antrian sistem
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-2 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full text-xs text-green-700 font-semibold">
                <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse inline-block"></span>
                Live Update
            </div>
        </div>

        @php
            $filterDate = request('date');
            $filterPoly = (int) request('poly_id', 0);
            $filterStatus = request('status', '');

            $query = \App\Models\Queue::with(['patient', 'poly', 'doctor.user', 'survey']);

            // Role-based filtering
            if ($user->role === 'patient') {
                $query->where('patient_id', $user->id);
            } elseif ($user->role === 'doctor') {
                $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
                if ($doctor) {
                    $query->where('doctor_id', $doctor->id);
                }
            }

            if ($filterDate) {
                $query->whereDate('queue_date', $filterDate);
            }
            if ($filterPoly) {
                $query->where('poly_id', $filterPoly);
            }
            if ($filterStatus) {
                $query->where('status', $filterStatus);
            }

            $queues = $query->orderBy('queue_date', 'desc')
                ->orderBy('queue_number')
                ->limit(100)
                ->get();

            $polies = \App\Models\Poly::where('is_active', true)->get();

            $activeQueue = null;
            if ($user->role === 'patient') {
                $activeQueue = \App\Models\Queue::where('patient_id', $user->id)
                    ->whereDate('queue_date', today())
                    ->whereNotIn('status', ['done', 'cancelled'])
                    ->first();
            }
        @endphp

        <!-- Active Queue Card (patient only) -->
        @if($activeQueue)
            @php $sm = $activeQueue->status_meta; @endphp
            <div class="bg-gradient-to-r from-primary to-primary-light text-white rounded-2xl p-5 mb-6 flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-3xl font-extrabold flex-shrink-0">
                    #{{ $activeQueue->queue_number }}
                </div>
                <div class="flex-1">
                    <p class="text-xs opacity-75 mb-0.5">Antrian Aktif Hari Ini</p>
                    <p class="font-bold text-lg">Estimasi: {{ $activeQueue->estimated_time ? date('H:i', strtotime($activeQueue->estimated_time)) : '—' }}</p>
                    <span id="q-status-badge" class="mt-1 inline-block px-3 py-0.5 rounded-full text-xs font-semibold {{ $sm['tw'] }}">
                        {{ $sm['label'] }}
                    </span>
                </div>
                <div class="text-right text-xs opacity-75">
                    <div>Posisi antrian</div>
                    <div id="q-position" class="text-3xl font-extrabold opacity-100 text-white">—</div>
                </div>
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $filterDate }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Poliklinik</label>
                <select name="poly_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua Poli</option>
                    @foreach($polies as $p)
                        <option value="{{ $p->id }}" {{ $filterPoly == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua Status</option>
                    @foreach(config('klinik.queue_status_meta') as $k => $v)
                        <option value="{{ $k }}" {{ $filterStatus == $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                    <i class="fa-solid fa-filter mr-1"></i>Filter
                </button>
                <a href="{{ route('queue.history') }}" class="px-3 py-2 border border-gray-200 rounded-xl text-sm text-gray-500 hover:bg-gray-50">Reset</a>
            </div>
        </form>

        <!-- Queue Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" id="queue-table">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Antrian <span class="text-gray-400 font-normal">({{ $queues->count() }} data)</span></h3>
                <span class="text-xs text-gray-400" id="last-update">—</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-primary">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            @if($user->role !== 'patient')
                                <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                            @endif
                            <th class="px-4 py-3 text-left font-semibold">Poli</th>
                            <th class="px-4 py-3 text-left font-semibold">Dokter</th>
                            <th class="px-4 py-3 text-left font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-left font-semibold">Est. Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Survei</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50" id="queue-tbody">
                        @if($queues->isEmpty())
                            <tr>
                                <td colspan="8" class="px-4 py-10 text-center text-gray-400">
                                    <i class="fa-solid fa-inbox text-3xl mb-2 block text-gray-200"></i>
                                    Tidak ada data antrian.
                                </td>
                            </tr>
                        @endif
                        @foreach($queues as $q)
                            @php $sm = $q->status_meta; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-white text-xs" style="background:{{ $q->poly->color }}">
                                        #{{ $q->queue_number }}
                                    </span>
                                </td>
                                @if($user->role !== 'patient')
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-xs">{{ $q->patient->username }}</div>
                                        <div class="text-xs text-gray-400">{{ $q->patient->phone }}</div>
                                    </td>
                                @endif
                                <td class="px-4 py-3">
                                    <span class="text-xs font-medium" style="color:{{ $q->poly->color }}">
                                        <i class="fa-solid {{ $q->poly->icon }} mr-1"></i>{{ $q->poly->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">dr. {{ $q->doctor->user->username }}</td>
                                <td class="px-4 py-3 text-xs">{{ date('d M Y', strtotime($q->queue_date)) }}</td>
                                <td class="px-4 py-3 text-xs font-medium">{{ $q->estimated_time ? date('H:i', strtotime($q->estimated_time)) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sm['tw'] }}">
                                        {{ $sm['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($q->status === 'done' && !$q->survey && $q->patient_id == $user->id)
                                        <a href="{{ route('survey.form', $q->id) }}"
                                           class="inline-flex items-center gap-1 bg-amber-50 text-amber-600 hover:bg-amber-100 px-2 py-1 rounded-lg text-xs font-semibold">
                                            <i class="fa-solid fa-star"></i>Isi Survei
                                        </a>
                                    @elseif($q->survey)
                                        <span class="text-green-600 text-xs"><i class="fa-solid fa-check-circle mr-1"></i>Terisi</span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

@if($activeQueue)
    @section('scripts')
    <script>
        startQueuePoll({{ $activeQueue->id }});
    </script>
    @endsection
@endif

<script>
    (function(){
        const el = document.getElementById('last-update');
        function refreshTime(){ if(el) el.textContent = 'Diperbarui: ' + new Date().toLocaleTimeString('id-ID'); }
        refreshTime();
        setInterval(()=>{ location.reload(); }, 30000);
    })();
</script>
@endsection