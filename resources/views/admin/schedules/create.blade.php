@extends('layouts.app')

@section('title', 'Tambah Jadwal')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Tambah Jadwal Praktik</h1>
        <p class="text-gray-400 text-sm mb-5">Buat jadwal praktik baru untuk dokter</p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form method="POST" action="{{ route('admin.schedules.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Dokter</label>
                        <select name="doctor_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Dokter</option>
                            @foreach($doctors as $d)
                                <option value="{{ $d->id }}">{{ $d->user->username }} — {{ $d->poly->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Hari</label>
                        <select name="day_of_week" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                            @foreach(config('klinik.days_id') as $i => $day)
                                <option value="{{ $i }}">{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required value="08:00"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required value="12:00"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Maks. Slot Antrian</label>
                        <input type="number" name="max_slots" min="1" max="100" value="20"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (opsional)</label>
                        <input type="text" name="notes" placeholder="cth: Khusus BPJS"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                        Simpan
                    </button>
                    <a href="{{ route('admin.schedules.index') }}" class="ml-2 px-6 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection