@extends('layouts.app')

@section('title', 'Pasien Hari Ini')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-doctor')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Daftar Pasien</h1>
        <p class="text-gray-400 text-sm mb-5">Konfirmasi status pasien Anda</p>

        @php
            $doctor = auth()->user()->doctor;
            $filterDate = request('date', date('Y-m-d'));
            $filterStatus = request('status', '');

            $query = \App\Models\Queue::with('patient')
                ->where('doctor_id', $doctor->id)
                ->whereDate('queue_date', $filterDate);

            if ($filterStatus) {
                $query->where('status', $filterStatus);
            }

            $queues = $query->orderBy('queue_number')->get();
        @endphp

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Filter -->
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $filterDate }}"
                       class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua</option>
                    @foreach(config('klinik.queue_status_meta') as $k => $v)
                        <option value="{{ $k }}" {{ $filterStatus == $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                <i class="fa-solid fa-filter mr-1"></i>Filter
            </button>
        </form>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Pasien {{ date('d M Y', strtotime($filterDate)) }} ({{ $queues->count() }})</h3>
            </div>
            @if($queues->isEmpty())
                <div class="py-10 text-center text-gray-400"><i class="fa-solid fa-inbox text-3xl mb-2 block"></i>Tidak ada data pasien.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-cyan-50 text-cyan-700">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">#</th>
                                <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                                <th class="px-4 py-3 text-left font-semibold">Est. Waktu</th>
                                <th class="px-4 py-3 text-left font-semibold">Dipanggil</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($queues as $q)
                                @php $sm = $q->status_meta; @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-white text-xs" style="background:{{ $doctor->poly->color }}">{{ $q->queue_number }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-semibold">{{ $q->patient->username }}</div>
                                        <div class="text-xs text-gray-400">{{ $q->patient->phone }}</div>
                                    </td>
                                    <td class="px-4 py-3 text-xs">{{ $q->estimated_time ? date('H:i', strtotime($q->estimated_time)) : '—' }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $q->called_at ? date('H:i', strtotime($q->called_at)) : '—' }}</td>
                                    <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span></td>
                                    <td class="px-4 py-3">
                                        <form method="POST" action="{{ route('doctor.patients.updateStatus') }}" class="flex gap-1">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                            @if($q->status === 'called')
                                                <button name="status" value="in_progress" class="text-xs bg-indigo-50 text-indigo-600 hover:bg-indigo-100 px-2 py-1 rounded-lg font-semibold"><i class="fa-solid fa-stethoscope mr-1"></i>Periksa</button>
                                            @elseif($q->status === 'in_progress')
                                                <button name="status" value="done" class="text-xs bg-green-50 text-green-600 hover:bg-green-100 px-2 py-1 rounded-lg font-semibold"><i class="fa-solid fa-circle-check mr-1"></i>Selesai</button>
                                            @endif
                                            @if(in_array($q->status, ['waiting', 'called']))
                                                <button name="status" value="late" class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-2 py-1 rounded-lg font-semibold"><i class="fa-solid fa-clock mr-1"></i>Terlambat</button>
                                            @endif
                                        </form>
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