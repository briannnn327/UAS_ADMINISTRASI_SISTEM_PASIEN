@extends('layouts.app')

@section('title', 'Edit Pengguna')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Edit Pengguna</h1>
        <p class="text-gray-400 text-sm mb-5">Edit data pengguna: {{ $user->username }}</p>

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-5">
                @foreach($errors->all() as $error)
                    <p><i class="fa-solid fa-circle-exclamation mr-1"></i>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                @csrf @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" value="{{ old('username', $user->username) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            @foreach(['patient' => 'Pasien', 'doctor' => 'Dokter', 'admin' => 'Admin'] as $k => $l)
                                <option value="{{ $k }}" {{ old('role', $user->role) == $k ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
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
                    <a href="{{ route('admin.users.index') }}" class="ml-2 px-6 py-2.5 border border-gray-200 rounded-xl text-sm text-gray-600 hover:bg-gray-50 transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection