@extends('layouts.app')

@section('title', 'Jadwal Saya')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-doctor')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Jadwal Praktik Saya</h1>
        <p class="text-gray-400 text-sm mb-5">Konfirmasi ketersediaan slot praktik Anda</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        @php
            $doctor = auth()->user()->doctor;
            $schedules = \App\Models\Schedule::where('doctor_id', $doctor->id)
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            $queueCounts = \App\Models\Queue::where('doctor_id', $doctor->id)
                ->whereDate('queue_date', '>=', today())
                ->whereDate('queue_date', '<=', today()->addDays(7))
                ->whereNotIn('status', ['cancelled'])
                ->selectRaw('DAYOFWEEK(queue_date) - 1 as dow, COUNT(*) as total')
                ->groupBy('dow')
                ->pluck('total', 'dow')
                ->toArray();
        @endphp

        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($schedules as $sc)
                @php
                    $qCount = $queueCounts[$sc->day_of_week] ?? 0;
                    $pct = $sc->max_slots > 0 ? min(100, round($qCount / $sc->max_slots * 100)) : 0;
                    $isToday = date('w') == $sc->day_of_week;
                @endphp
                <div class="bg-white rounded-2xl border {{ $isToday ? 'border-cyan-300 shadow-cyan-100' : 'border-gray-100' }} shadow-sm p-5">
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-bold text-gray-800 {{ $isToday ? 'text-cyan-700' : '' }}">
                            {{ $isToday ? '📅 ' : '' }}{{ config('klinik.days_id')[$sc->day_of_week] ?? '' }}
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $sc->is_available ? 'Tersedia' : 'Tutup' }}
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 mb-1"><i class="fa-solid fa-clock mr-1 text-gray-400"></i>{{ date('H:i', strtotime($sc->start_time)) }} – {{ date('H:i', strtotime($sc->end_time)) }}</div>
                    <div class="text-xs text-gray-400 mb-3"><i class="fa-solid fa-users mr-1"></i>Maks. {{ $sc->max_slots }} pasien</div>

                    <!-- Slot usage -->
                    <div class="mb-1 flex justify-between text-xs text-gray-500">
                        <span>Terpakai (7 hari ke depan)</span><span>{{ $qCount }}/{{ $sc->max_slots }}</span>
                    </div>
                    <div class="bg-gray-100 rounded-full h-2 mb-4">
                        <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $doctor->poly->color }}"></div>
                    </div>

                    @if($sc->notes)
                        <p class="text-xs text-gray-400 italic mb-3">{{ $sc->notes }}</p>
                    @endif

                    <form method="POST" action="{{ route('doctor.schedule.toggle') }}" class="flex gap-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="schedule_id" value="{{ $sc->id }}">
                        @if($sc->is_available)
                            <button name="is_available" value="0" class="flex-1 text-xs bg-red-50 text-red-600 hover:bg-red-100 py-1.5 rounded-xl font-semibold transition">
                                <i class="fa-solid fa-times mr-1"></i>Tutup Slot
                            </button>
                        @else
                            <button name="is_available" value="1" class="flex-1 text-xs bg-green-50 text-green-600 hover:bg-green-100 py-1.5 rounded-xl font-semibold transition">
                                <i class="fa-solid fa-check mr-1"></i>Buka Slot
                            </button>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>
    </main>
</div>
@endsection