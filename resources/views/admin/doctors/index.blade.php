@extends('layouts.app')

@section('title', 'Manajemen Dokter')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Manajemen Dokter</h1>
        <p class="text-gray-400 text-sm mb-5">Kelola data dan ketersediaan dokter</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tambah Dokter Baru</h3>
                <form method="POST" action="{{ route('admin.doctors.store') }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Username</label>
                            <input type="text" name="username" required
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Email</label>
                            <input type="email" name="email" required
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">No. HP / WA</label>
                            <input type="tel" name="phone"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Poliklinik</label>
                            <select name="poly_id" required
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="">Pilih Poli</option>
                                @foreach($polies as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Spesialisasi</label>
                            <input type="text" name="specialization" placeholder="cth: Dokter Spesialis Anak"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">No. SIP / Lisensi</label>
                            <input type="text" name="license_number" placeholder="SIP-001/2024"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Bio Singkat</label>
                            <textarea name="bio" rows="2" placeholder="Pengalaman dan keahlian dokter..."
                                      class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                            <input type="password" name="password" required placeholder="Min. 8 karakter"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition mt-3">
                        Tambah Dokter
                    </button>
                </form>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-gray-800">Daftar Dokter ({{ $doctors->count() }})</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-primary">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Dokter</th>
                                <th class="px-4 py-3 text-left font-semibold">Poli</th>
                                <th class="px-4 py-3 text-left font-semibold">Spesialisasi</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($doctors as $d)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <div class="font-semibold">dr. {{ $d->user->username }}</div>
                                    <div class="text-xs text-gray-400">{{ $d->user->email }}</div>
                                    <div class="text-xs text-gray-400">{{ $d->user->phone ?? '' }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold text-white" style="background:{{ $d->poly->color }}">
                                        {{ $d->poly->name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-600">{{ $d->specialization ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $d->is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                        {{ $d->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-1">
                                        <a href="{{ route('admin.doctors.edit', $d) }}" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs" title="Edit">
                                            <i class="fa-solid fa-pen"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.doctors.toggle', $d) }}" class="inline">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="p-1.5 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 text-xs" title="Toggle Ketersediaan">
                                                <i class="fa-solid fa-toggle-{{ $d->is_available ? 'on' : 'off' }}"></i>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.doctors.destroy', $d) }}" class="inline"
                                              onsubmit="return confirm('Hapus dokter ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
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