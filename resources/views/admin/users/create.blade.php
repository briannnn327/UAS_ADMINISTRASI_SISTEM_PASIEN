@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Tambah Pengguna</h1>
        <p class="text-gray-400 text-sm mb-5">Buat akun pengguna baru</p>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 max-w-2xl">
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Username</label>
                        <input type="text" name="username" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" required
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">No. HP / WhatsApp</label>
                        <input type="tel" name="phone"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Role</label>
                        <select name="role" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="patient">Pasien</option>
                            <option value="doctor">Dokter</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                        <input type="password" name="password" required placeholder="Min. 8 karakter"
                               class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>
                    <button type="submit" class="bg-primary text-white px-6 py-2.5 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                        Simpan
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