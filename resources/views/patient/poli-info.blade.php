@extends('layouts.app')

@section('title', 'Info Poli & Jadwal')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-patient')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Info Poli & Jadwal Dokter</h1>
        <p class="text-gray-400 text-sm mb-5">Jumlah antrian real-time dan jadwal praktik dokter</p>

        @php
            $polies = \App\Models\Poly::where('is_active', true)->get();
            $selectedId = request('id', $polies->first()->id ?? 0);
            $selectedPoly = $polies->firstWhere('id', $selectedId);

            $doctors = [];
            $schedules = [];
            $queueCounts = \App\Models\Queue::whereDate('queue_date', today())
                ->whereNotIn('status', ['cancelled'])
                ->selectRaw('poly_id, COUNT(*) as total')
                ->groupBy('poly_id')
                ->pluck('total', 'poly_id')
                ->toArray();

            if ($selectedPoly) {
                $doctors = $selectedPoly->doctors()
                    ->with('user')
                    ->where('is_available', true)
                    ->get();

                foreach ($doctors as $doctor) {
                    $schedules[$doctor->id] = $doctor->schedules()
                        ->orderBy('day_of_week')
                        ->orderBy('start_time')
                        ->get();
                }
            }
        @endphp

        <!-- Poly selector -->
        <div class="flex flex-wrap gap-2 mb-6">
            @foreach($polies as $p)
                <a href="{{ route('patient.poli-info', ['id' => $p->id]) }}"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold transition border-2
                          {{ $p->id == $selectedId ? 'text-white border-transparent' : 'bg-white border-gray-200 text-gray-600 hover:border-gray-300' }}"
                   style="{{ $p->id == $selectedId ? 'background:'.$p->color.';border-color:'.$p->color : '' }}">
                    <i class="fa-solid {{ $p->icon }}"></i>
                    {{ $p->name }}
                    <span class="text-xs {{ $p->id == $selectedId ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500' }} px-1.5 py-0.5 rounded-full font-bold">
                        {{ $queueCounts[$p->id] ?? 0 }}
                    </span>
                </a>
            @endforeach
        </div>

        @if($selectedPoly)
        <!-- Poly header card -->
        <div class="rounded-2xl p-6 mb-6 text-white" style="background:linear-gradient(135deg, {{ $selectedPoly->color }}, {{ $selectedPoly->color }}bb)">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-3xl">
                    <i class="fa-solid {{ $selectedPoly->icon }}"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold">{{ $selectedPoly->name }}</h2>
                    <p class="text-sm opacity-80">{{ $selectedPoly->description }}</p>
                </div>
                <div class="ml-auto text-center">
                    <div class="text-4xl font-extrabold">{{ $queueCounts[$selectedId] ?? 0 }}</div>
                    <div class="text-xs opacity-75">Antrian Aktif Hari Ini</div>
                </div>
            </div>
        </div>

        <!-- Doctors grid -->
        <h3 class="font-bold text-gray-800 mb-3">Dokter & Jadwal Praktik</h3>
        @if($doctors->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center text-gray-400">
                <i class="fa-solid fa-user-doctor text-3xl mb-2 block"></i>
                Tidak ada dokter aktif di poli ini.
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            @foreach($doctors as $d)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center text-white font-extrabold text-lg" style="background:{{ $selectedPoly->color }}">
                        {{ strtoupper(substr($d->user->username, 0, 1)) }}
                    </div>
                    <div>
                        <div class="font-bold text-gray-900">dr. {{ $d->user->username }}</div>
                        <div class="text-xs text-gray-500">{{ $d->specialization ?? 'Dokter' }}</div>
                        <div class="text-xs text-gray-400">{{ $d->license_number ?? '' }}</div>
                    </div>
                    <span class="ml-auto text-xs font-semibold {{ $d->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }} px-2 py-0.5 rounded-full">
                        {{ $d->is_available ? '● Aktif' : '● Tidak Aktif' }}
                    </span>
                </div>

                @if($d->bio)
                    <p class="text-xs text-gray-500 mb-3 italic">{{ $d->bio }}</p>
                @endif

                <!-- Schedules -->
                <div class="space-y-2">
                    <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Jadwal Praktik</div>
                    @if(!empty($schedules[$d->id]) && $schedules[$d->id]->count())
                        @foreach($schedules[$d->id] as $sc)
                            @php $isToday = date('w') == $sc->day_of_week; @endphp
                            <div class="flex items-center gap-2 text-xs {{ !$sc->is_available ? 'opacity-50' : '' }}">
                                <span class="w-20 font-medium {{ $isToday ? 'text-primary font-bold' : 'text-gray-600' }}">
                                    {{ $isToday ? '→ ' : '' }}{{ config('klinik.days_id')[$sc->day_of_week] ?? '' }}
                                </span>
                                <span class="text-gray-500">{{ date('H:i', strtotime($sc->start_time)) }} – {{ date('H:i', strtotime($sc->end_time)) }}</span>
                                <span class="ml-auto {{ $sc->is_available ? 'text-green-600' : 'text-red-400' }}">{{ $sc->is_available ? 'Tersedia' : 'Tutup' }}</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-xs text-gray-400">Jadwal belum diatur.</p>
                    @endif
                </div>

                <a href="{{ route('patient.register-queue', ['doctor_id' => $d->id]) }}"
                   class="mt-4 block text-center bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                    <i class="fa-solid fa-ticket mr-1"></i>Daftar Antrian
                </a>
            </div>
            @endforeach
        </div>
        @endif
    </main>
</div>
@endsection