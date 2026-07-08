@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900">Dashboard Admin</h1>
                <p class="text-gray-400 text-sm">Selamat datang, {{ auth()->user()->username }}! — {{ date('l, d F Y') }}</p>
            </div>
            <a href="{{ route('admin.queues.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                <i class="fa-solid fa-ticket"></i>Kelola Antrian
            </a>
        </div>

        @php
            $stats = [
                'patients' => \App\Models\User::where('role', 'patient')->count(),
                'doctors'  => \App\Models\User::where('role', 'doctor')->count(),
                'today'    => \App\Models\Queue::whereDate('queue_date', today())->count(),
                'done'     => \App\Models\Queue::whereDate('queue_date', today())->where('status', 'done')->count(),
                'waiting'  => \App\Models\Queue::whereDate('queue_date', today())->where('status', 'waiting')->count(),
                'surveys'  => \App\Models\Survey::count(),
            ];

            $polyToday = \App\Models\Poly::withCount([
                'queues as total' => function($q) { $q->whereDate('queue_date', today()); },
                'queues as done' => function($q) { $q->whereDate('queue_date', today())->where('status', 'done'); },
                'queues as waiting' => function($q) { $q->whereDate('queue_date', today())->where('status', 'waiting'); }
            ])->where('is_active', true)->get();

            $recent = \App\Models\Queue::with(['patient', 'poly', 'doctor.user'])
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        @endphp

        <!-- Stat cards -->
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            @foreach([
                ['Pasien', $stats['patients'], 'fa-users', 'from-blue-500 to-blue-600'],
                ['Dokter', $stats['doctors'], 'fa-user-doctor', 'from-cyan-500 to-cyan-600'],
                ['Antrian Hari Ini', $stats['today'], 'fa-ticket', 'from-yellow-400 to-orange-500'],
                ['Selesai', $stats['done'], 'fa-circle-check', 'from-green-500 to-teal-500'],
                ['Menunggu', $stats['waiting'], 'fa-clock', 'from-indigo-500 to-violet-500'],
                ['Total Survei', $stats['surveys'], 'fa-star', 'from-pink-500 to-rose-500'],
            ] as [$lbl, $val, $ico, $grad])
            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br {{ $grad }} flex items-center justify-center text-white text-sm mb-3">
                    <i class="fa-solid {{ $ico }}"></i>
                </div>
                <div class="text-2xl font-extrabold text-gray-900">{{ $val }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $lbl }}</div>
            </div>
            @endforeach
        </div>

        <div class="grid lg:grid-cols-5 gap-6">
            <!-- Poly Progress -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
                <h3 class="font-bold text-gray-800 mb-4">Antrian Per Poli Hari Ini</h3>
                @forelse($polyToday as $ps)
                    @php $pct = $ps->total > 0 ? round($ps->done / $ps->total * 100) : 0; @endphp
                    <div class="mb-4">
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium" style="color:{{ $ps->color }}">
                                <i class="fa-solid {{ $ps->icon }} mr-1"></i>{{ $ps->name }}
                            </span>
                            <span class="text-gray-400">{{ $ps->done }}/{{ $ps->total ?: 0 }}</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $ps->color }}"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm text-center py-4">Belum ada antrian hari ini</p>
                @endforelse
            </div>

            <!-- Recent queues -->
            <div class="lg:col-span-3 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Antrian Terbaru</h3>
                    <a href="{{ route('admin.queues.index') }}" class="text-primary text-xs font-medium">Lihat semua →</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-primary">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">#</th>
                                <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                                <th class="px-4 py-3 text-left font-semibold">Poli</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @forelse($recent as $q)
                                @php $sm = $q->status_meta; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="w-8 h-8 rounded-lg bg-primary text-white flex items-center justify-center font-bold text-xs">
                                            {{ $q->queue_number }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-medium">{{ $q->patient->username }}</td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $q->poly->name }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($q->status === 'waiting')
                                            <form action="{{ route('admin.queues.call') }}" method="POST"
                                                  onsubmit="return confirm('Panggil antrian ini?')" class="inline">
                                                @csrf
                                                <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                                <button type="submit"
                                                        class="text-xs bg-primary text-white px-2 py-1 rounded-lg hover:bg-primary-light transition">
                                                    <i class="fa-solid fa-bullhorn mr-1"></i>Panggil
                                                </button>
                                            </form>
                                        @elseif($q->status === 'called')
                                            <form action="{{ route('admin.queues.updateStatus') }}" method="POST"
                                                  onsubmit="return confirm('Tandai selesai?')" class="inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                                <input type="hidden" name="status" value="done">
                                                <button type="submit"
                                                        class="text-xs bg-green-600 text-white px-2 py-1 rounded-lg hover:bg-green-700 transition">
                                                    <i class="fa-solid fa-check mr-1"></i>Selesai
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                                        <i class="fa-solid fa-inbox text-2xl mb-2 block"></i>
                                        Belum ada antrian
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection