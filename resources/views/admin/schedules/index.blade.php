@extends('layouts.app')

@section('title', 'Jadwal Praktik')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Jadwal Praktik Dokter</h1>
        <p class="text-gray-400 text-sm mb-5">Atur jadwal praktik dan ketersediaan slot antrian</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tambah Jadwal Baru</h3>
                <form method="POST" action="{{ route('admin.schedules.store') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Dokter</label>
                            <select name="doctor_id" required class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Pilih Dokter</option>
                                @foreach($doctors as $d)
                                    <option value="{{ $d->id }}">{{ $d->user->username }} — {{ $d->poly->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Hari</label>
                            <select name="day_of_week" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                                @foreach(config('klinik.days_id') as $i => $day)
                                    <option value="{{ $i }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Jam Mulai</label>
                                <input type="time" name="start_time" required value="08:00"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Jam Selesai</label>
                                <input type="time" name="end_time" required value="12:00"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Maks. Slot Antrian</label>
                            <input type="number" name="max_slots" min="1" max="100" value="20"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Catatan (opsional)</label>
                            <input type="text" name="notes" placeholder="cth: Khusus BPJS"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition mt-3">
                        Tambah Jadwal
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Daftar Jadwal ({{ $schedules->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-primary">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Dokter</th>
                                <th class="px-4 py-3 text-left font-semibold">Poli</th>
                                <th class="px-4 py-3 text-left font-semibold">Hari</th>
                                <th class="px-4 py-3 text-left font-semibold">Waktu</th>
                                <th class="px-4 py-3 text-left font-semibold">Slot</th>
                                <th class="px-4 py-3 text-left font-semibold">Antrian</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($schedules as $s)
                                @php
                                    $used = $usedCounts[$s->id] ?? 0;
                                    $pct = $s->max_slots > 0 ? min(100, round($used / $s->max_slots * 100)) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-semibold text-xs">dr. {{ $s->doctor->user->username }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs text-white font-semibold" style="background:{{ $s->doctor->poly->color }}">
                                            {{ $s->doctor->poly->name }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-xs font-medium">{{ config('klinik.days_id')[$s->day_of_week] ?? '' }}</td>
                                    <td class="px-4 py-3 text-xs">{{ date('H:i', strtotime($s->start_time)) }} – {{ date('H:i', strtotime($s->end_time)) }}</td>
                                    <td class="px-4 py-3 text-xs">{{ $s->max_slots }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-100 rounded-full h-1.5 w-16">
                                                <div class="h-1.5 rounded-full bg-primary" style="width:{{ $pct }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500">{{ $used }}/{{ $s->max_slots }}</span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $s->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                            {{ $s->is_available ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <a href="{{ route('admin.schedules.edit', $s) }}" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs"><i class="fa-solid fa-pen"></i></a>
                                            <form method="POST" action="{{ route('admin.schedules.toggle', $s) }}" class="inline">
                                                @csrf @method('PATCH')
                                                <button type="submit" class="p-1.5 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 text-xs"><i class="fa-solid fa-power-off"></i></button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.schedules.destroy', $s) }}" class="inline"
                                                  onsubmit="return confirm('Hapus jadwal ini?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>
@endsection