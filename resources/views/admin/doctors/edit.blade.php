@extends('layouts.app')

@section('title', 'Edit Dokter')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Edit Dokter</h1>
        <p class="text-gray-400 text-sm mb-5">Edit data dokter: dr. {{ $doctor->user->username }}</p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form method="POST" action="{{ route('admin.doctors.update', $doctor) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username', $doctor->user->username) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $doctor->user->email) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WA</label>
                        <input type="tel" name="phone" value="{{ old('phone', $doctor->user->phone) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Poliklinik</label>
                        <select name="poly_id" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            @foreach($polies as $p)
                                <option value="{{ $p->id }}" {{ old('poly_id', $doctor->poly_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Spesialisasi</label>
                        <input type="text" name="specialization" value="{{ old('specialization', $doctor->specialization) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. SIP / Lisensi</label>
                        <input type="text" name="license_number" value="{{ old('license_number', $doctor->license_number) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Bio Singkat</label>
                        <textarea name="bio" rows="3" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">{{ old('bio', $doctor->bio) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Ketersediaan</label>
                        <select name="is_available" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm">
                            <option value="1" {{ old('is_available', $doctor->is_available) ? 'selected' : '' }}>Tersedia</option>
                            <option value="0" {{ !old('is_available', $doctor->is_available) ? 'selected' : '' }}>Tidak Tersedia</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="new_password" placeholder="Min. 8 karakter"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('admin.doctors.index') }}" class="ml-2 px-6 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection