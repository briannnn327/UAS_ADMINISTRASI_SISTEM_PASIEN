@extends('layouts.app')

@section('title', 'Dashboard Dokter')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-doctor')

    <main class="flex-1 p-6 overflow-x-hidden">
        <div class="flex items-start justify-between flex-wrap gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Dashboard Dokter</h1>
                <p class="text-gray-400 text-sm">Selamat datang, dr. {{ auth()->user()->username }} — {{ date('l, d F Y') }}</p>
                @php
                    $doctor = auth()->user()->doctor;
                    $todaySchedule = \App\Models\Schedule::where('doctor_id', $doctor->id)
                        ->where('day_of_week', date('w'))
                        ->where('is_available', true)
                        ->first();
                    $todayQueues = \App\Models\Queue::with('patient')
                        ->where('doctor_id', $doctor->id)
                        ->whereDate('queue_date', today())
                        ->orderBy('queue_number')
                        ->get();
                    $stats = [
                        'waiting' => $todayQueues->where('status', 'waiting')->count(),
                        'called' => $todayQueues->whereIn('status', ['called', 'in_progress'])->count(),
                        'done' => $todayQueues->where('status', 'done')->count(),
                        'total' => $todayQueues->count(),
                    ];
                    $schedules = \App\Models\Schedule::where('doctor_id', $doctor->id)
                        ->orderBy('day_of_week')
                        ->orderBy('start_time')
                        ->get();
                @endphp
                <span class="inline-flex items-center gap-1 mt-1 px-3 py-1 rounded-full text-xs font-semibold text-white" style="background:{{ $doctor->poly->color }}">
                    <i class="fa-solid fa-hospital-user"></i>{{ $doctor->poly->name }}
                </span>
            </div>
            @if($todaySchedule)
                <div class="bg-cyan-50 border border-cyan-200 rounded-2xl p-4 text-sm">
                    <div class="font-semibold text-cyan-700 mb-1"><i class="fa-solid fa-clock mr-1"></i>Jadwal Hari Ini</div>
                    <div class="text-cyan-600">{{ date('H:i', strtotime($todaySchedule->start_time)) }} – {{ date('H:i', strtotime($todaySchedule->end_time)) }}</div>
                    <div class="text-xs text-cyan-500">Maks. {{ $todaySchedule->max_slots }} pasien</div>
                </div>
            @else
                <div class="bg-gray-50 border border-gray-200 rounded-2xl p-4 text-sm text-gray-500">
                    <i class="fa-solid fa-calendar-xmark mr-1"></i>Tidak ada jadwal hari ini
                </div>
            @endif
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @foreach([
                ['Total Hari Ini', $stats['total'], 'fa-users', 'from-blue-500 to-blue-600'],
                ['Menunggu', $stats['waiting'], 'fa-clock', 'from-yellow-400 to-orange-500'],
                ['Sedang Proses', $stats['called'], 'fa-stethoscope', 'from-cyan-500 to-cyan-600'],
                ['Selesai', $stats['done'], 'fa-circle-check', 'from-green-500 to-teal-500'],
            ] as [$lbl, $v, $ico, $g])
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $g }} flex items-center justify-center text-white text-sm mb-2">
                    <i class="fa-solid {{ $ico }}"></i>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $v }}</div>
                <div class="text-xs text-gray-400">{{ $lbl }}</div>
            </div>
            @endforeach
        </div>

        <!-- Doctor Schedule Toggle -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-6">
            <h3 class="font-bold text-gray-800 mb-3"><i class="fa-solid fa-calendar-check mr-2 text-cyan-600"></i>Konfirmasi Ketersediaan Jadwal</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cyan-50 text-cyan-700">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Hari</th>
                            <th class="px-4 py-2 text-left font-semibold">Waktu</th>
                            <th class="px-4 py-2 text-left font-semibold">Maks Slot</th>
                            <th class="px-4 py-2 text-left font-semibold">Status</th>
                            <th class="px-4 py-2 text-left font-semibold">Konfirmasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($schedules as $sc)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-medium">{{ config('klinik.days_id')[$sc->day_of_week] ?? '' }}</td>
                                <td class="px-4 py-2 text-gray-600">{{ date('H:i', strtotime($sc->start_time)) }} – {{ date('H:i', strtotime($sc->end_time)) }}</td>
                                <td class="px-4 py-2">{{ $sc->max_slots }} pasien</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sc->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $sc->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('doctor.schedule.toggle') }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="schedule_id" value="{{ $sc->id }}">
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-semibold transition
                                                       {{ $sc->is_available ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' }}">
                                            <i class="fa-solid fa-{{ $sc->is_available ? 'times' : 'check' }}"></i>
                                            {{ $sc->is_available ? 'Tutup Slot' : 'Buka Slot' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Today's queue list -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800"><i class="fa-solid fa-ticket mr-2 text-cyan-600"></i>Antrian Pasien Hari Ini</h3>
                <a href="{{ route('doctor.patients.index') }}" class="text-primary text-xs font-medium">Lihat detail →</a>
            </div>
            @if($todayQueues->isEmpty())
                <div class="py-10 text-center text-gray-400">
                    <i class="fa-solid fa-inbox text-3xl mb-2 block"></i>Belum ada pasien hari ini.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-cyan-50 text-cyan-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">#</th>
                                <th class="px-4 py-3 text-left font-semibold">Nama Pasien</th>
                                <th class="px-4 py-3 text-left font-semibold">Est. Jam</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Konfirmasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($todayQueues as $q)
                                @php $sm = $q->status_meta; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="w-8 h-8 rounded-xl flex items-center justify-center font-extrabold text-white text-xs" style="background:{{ $doctor->poly->color }}">
                                            {{ $q->queue_number }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $q->patient->username }}</td>
                                    <td class="px-4 py-3 text-gray-500">{{ $q->estimated_time ? date('H:i', strtotime($q->estimated_time)) : '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($q->status === 'called')
                                            <form method="POST" action="{{ route('doctor.patients.updateStatus') }}" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                                <input type="hidden" name="status" value="in_progress">
                                                <button type="submit" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2 py-1 rounded-lg font-semibold">
                                                    <i class="fa-solid fa-stethoscope mr-1"></i>Mulai Periksa
                                                </button>
                                            </form>
                                        @elseif($q->status === 'in_progress')
                                            <form method="POST" action="{{ route('doctor.patients.updateStatus') }}" class="inline">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                                <input type="hidden" name="status" value="done">
                                                <button type="submit" class="text-xs bg-green-50 text-green-600 hover:bg-green-100 px-2 py-1 rounded-lg font-semibold">
                                                    <i class="fa-solid fa-circle-check mr-1"></i>Selesai
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
</div>
@endsection