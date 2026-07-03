@extends('layouts.app')

@section('title', 'Riwayat Pasien')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-doctor')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Riwayat Pasien</h1>
        <p class="text-gray-400 text-sm mb-5">Histori antrian pasien yang pernah Anda tangani</p>

        @php
            $doctor = auth()->user()->doctor;
            $from = request('from', date('Y-m-d', strtotime('-7 days')));
            $to = request('to', date('Y-m-d'));
            $status = request('status', '');

            $query = \App\Models\Queue::with(['patient', 'survey'])
                ->where('doctor_id', $doctor->id)
                ->whereBetween('queue_date', [$from, $to]);

            if ($status) {
                $query->where('status', $status);
            }

            $history = $query->orderBy('queue_date', 'desc')
                ->orderBy('queue_number')
                ->get();
        @endphp

        <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Dari Tanggal</label>
                <input type="date" name="from" value="{{ $from }}"
                       class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Sampai Tanggal</label>
                <input type="date" name="to" value="{{ $to }}"
                       class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua</option>
                    @foreach(config('klinik.queue_status_meta') as $k => $v)
                        <option value="{{ $k }}" {{ $status == $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                <i class="fa-solid fa-filter mr-1"></i>Filter
            </button>
        </form>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Riwayat ({{ $history->count() }} data)</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-cyan-50 text-cyan-700">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Tgl</th>
                            <th class="px-4 py-3 text-left font-semibold">#</th>
                            <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                            <th class="px-4 py-3 text-left font-semibold">Est. Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Rating</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($history as $q)
                            @php $sm = $q->status_meta; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-xs">{{ date('d M Y', strtotime($q->queue_date)) }}</td>
                                <td class="px-4 py-3">
                                    <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-white text-xs" style="background:{{ $doctor->poly->color }}">{{ $q->queue_number }}</span>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $q->patient->username }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $q->estimated_time ? date('H:i', strtotime($q->estimated_time)) : '—' }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span></td>
                                <td class="px-4 py-3">
                                    @if($q->survey)
                                        <span class="text-yellow-500 font-bold">{{ $q->survey->overall_rating }}</span><span class="text-gray-400 text-xs">/5</span>
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
@endsection