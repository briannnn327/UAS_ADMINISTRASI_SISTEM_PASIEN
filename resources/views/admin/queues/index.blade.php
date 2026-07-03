@extends('layouts.app')

@section('title', 'Manajemen Antrian')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Manajemen Antrian</h1>
        <p class="text-gray-400 text-sm mb-5">Kelola dan panggil antrian pasien</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        <!-- Filters -->
        <form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-5 grid sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal</label>
                <input type="date" name="date" value="{{ $date ?? date('Y-m-d') }}"
                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Poli</label>
                <select name="poly_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua Poli</option>
                    @foreach($polies as $p)
                        <option value="{{ $p->id }}" {{ ($polyId ?? 0) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Status</label>
                <select name="status" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    <option value="">Semua Status</option>
                    @foreach(config('klinik.queue_status_meta') as $k => $v)
                        <option value="{{ $k }}" {{ ($status ?? '') == $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                    <i class="fa-solid fa-filter mr-1"></i>Filter
                </button>
            </div>
        </form>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">Daftar Antrian ({{ $queues->count() }})</h3>
                <span class="text-gray-400 text-sm">{{ date('d M Y', strtotime($date ?? date('Y-m-d'))) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 text-primary">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">#Antrian</th>
                            <th class="px-4 py-3 text-left font-semibold">Pasien</th>
                            <th class="px-4 py-3 text-left font-semibold">Poli</th>
                            <th class="px-4 py-3 text-left font-semibold">Dokter</th>
                            <th class="px-4 py-3 text-left font-semibold">Est. Waktu</th>
                            <th class="px-4 py-3 text-left font-semibold">Status</th>
                            <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @if($queues->isEmpty())
                            <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">Tidak ada data antrian.</td></tr>
                        @endif
                        @foreach($queues as $q)
                            @php $sm = $q->status_meta; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="w-9 h-9 rounded-xl flex items-center justify-center font-extrabold text-white text-sm" style="background:{{ $q->poly->color }}">{{ $q->queue_number }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $q->patient->username }}</div>
                                    <div class="text-xs text-gray-400">{{ $q->patient->phone }}</div>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500">{{ $q->poly->name }}</td>
                                <td class="px-4 py-3 text-xs text-gray-500">dr. {{ $q->doctor->user->username }}</td>
                                <td class="px-4 py-3 text-xs">{{ $q->estimated_time ? date('H:i', strtotime($q->estimated_time)) : '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $sm['tw'] }}">{{ $sm['label'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        @if($q->status === 'waiting')
                                            <form method="POST" action="{{ route('admin.queues.call') }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                                <button type="submit" onclick="return confirm('Panggil antrian ini?')"
                                                        class="inline-flex items-center gap-1 bg-primary text-white px-2.5 py-1 rounded-lg text-xs font-semibold hover:bg-primary-light transition">
                                                    <i class="fa-solid fa-bullhorn"></i>Panggil
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.queues.updateStatus') }}" class="inline">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="queue_id" value="{{ $q->id }}">
                                            <select name="status" onchange="this.form.submit()"
                                                    class="border border-gray-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-primary">
                                                @foreach(config('klinik.queue_status_meta') as $k => $v)
                                                    <option value="{{ $k }}" {{ $q->status == $k ? 'selected' : '' }}>{{ $v['label'] }}</option>
                                                @endforeach
                                            </select>
                                        </form>
                                    </div>
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