@extends('layouts.app')

@section('title', 'Edit Jadwal')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Edit Jadwal Praktik</h1>
        <p class="text-gray-400 text-sm mb-5">Edit jadwal praktik dokter</p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form method="POST" action="{{ route('admin.schedules.update', $schedule) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Dokter</label>
                        <p class="text-gray-600 text-sm">{{ $schedule->doctor->user->username }} — {{ $schedule->doctor->poly->name }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Hari</label>
                        <select name="day_of_week" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                            @foreach(config('klinik.days_id') as $i => $day)
                                <option value="{{ $i }}" {{ old('day_of_week', $schedule->day_of_week) == $i ? 'selected' : '' }}>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required value="{{ old('start_time', $schedule->start_time) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required value="{{ old('end_time', $schedule->end_time) }}"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Maks. Slot Antrian</label>
                        <input type="number" name="max_slots" min="1" max="100" value="{{ old('max_slots', $schedule->max_slots) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Status</label>
                        <select name="is_available" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                            <option value="1" {{ old('is_available', $schedule->is_available) ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ !old('is_available', $schedule->is_available) ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Catatan (opsional)</label>
                        <input type="text" name="notes" value="{{ old('notes', $schedule->notes) }}" placeholder="cth: Khusus BPJS"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                        Simpan Perubahan
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