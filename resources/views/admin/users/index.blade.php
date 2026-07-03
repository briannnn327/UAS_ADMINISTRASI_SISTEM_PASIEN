@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-admin')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Manajemen Pengguna</h1>
        <p class="text-gray-400 text-sm mb-5">Tambah, edit, dan kelola akun pengguna sistem</p>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-check"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-5 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>{{ session('error') }}
            </div>
        @endif

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4">Tambah Pengguna Baru</h3>
                <form method="POST" action="{{ route('admin.users.store') }}">
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
                            <label class="block text-xs font-semibold text-gray-500 mb-1">No. HP / WhatsApp</label>
                            <input type="tel" name="phone"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Role</label>
                            <select name="role" id="roleSelect"
                                    onchange="document.getElementById('doctor-fields').classList.toggle('hidden', this.value!=='doctor')"
                                    class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                                <option value="patient">Pasien</option>
                                <option value="doctor">Dokter</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div id="doctor-fields" class="hidden space-y-3 bg-cyan-50 p-3 rounded-xl">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Poliklinik</label>
                                <select name="poly_id" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                                    <option value="">Pilih Poli</option>
                                    @foreach(\App\Models\Poly::where('is_active', true)->get() as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">Spesialisasi</label>
                                <input type="text" name="specialization" placeholder="Dokter Umum"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 mb-1">No. SIP / Lisensi</label>
                                <input type="text" name="license_number" placeholder="SIP-XXX/2024"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Password</label>
                            <input type="password" name="password" required placeholder="Min. 8 karakter"
                                   class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        </div>
                    </div>

                    <div class="flex gap-2 mt-4">
                        <button type="submit" class="flex-1 bg-primary text-white py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                            Tambah Pengguna
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800">Daftar Pengguna ({{ $users->count() }})</h3>
                    <div class="flex gap-2 text-xs">
                        @php $counts = $users->groupBy('role')->map->count(); @endphp
                        @foreach(['admin'=>'blue','doctor'=>'cyan','patient'=>'green'] as $r=>$c)
                            <span class="px-2 py-1 bg-{{ $c }}-50 text-{{ $c }}-700 rounded-full font-semibold">
                                {{ ucfirst($r) }}: {{ $counts[$r] ?? 0 }}
                            </span>
                        @endforeach
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-blue-50 text-primary">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold">Pengguna</th>
                                <th class="px-4 py-3 text-left font-semibold">No. HP</th>
                                <th class="px-4 py-3 text-left font-semibold">Role</th>
                                <th class="px-4 py-3 text-left font-semibold">Status</th>
                                <th class="px-4 py-3 text-left font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($users as $u)
                                @php
                                    $roleColor = match($u->role) {
                                        'admin' => 'bg-blue-100 text-blue-700',
                                        'doctor' => 'bg-cyan-100 text-cyan-700',
                                        default => 'bg-green-100 text-green-700',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-bold text-xs">
                                                {{ strtoupper(substr($u->username, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-800">{{ $u->username }}</div>
                                                <div class="text-xs text-gray-400">{{ $u->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $u->phone ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $roleColor }}">{{ ucfirst($u->role) }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $u->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                                            {{ $u->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-1">
                                            <a href="{{ route('admin.users.edit', $u) }}" class="p-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs" title="Edit">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            @if($u->id != 1)
                                                @if($u->is_active)
                                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" onclick="return confirm('Nonaktifkan pengguna ini?')"
                                                                class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 text-xs" title="Nonaktifkan">
                                                            <i class="fa-solid fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('admin.users.toggle', $u) }}" class="inline">
                                                        @csrf @method('PATCH')
                                                        <button type="submit" class="p-1.5 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 text-xs" title="Aktifkan">
                                                            <i class="fa-solid fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
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