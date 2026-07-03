@extends('layouts.app')

@section('title', 'Beranda')

@section('content')
<!-- HERO -->
<section class="relative overflow-hidden bg-gradient-to-br from-blue-50 via-cyan-50 to-blue-100 py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Ilustrasi -->
            <div class="text-center">
                <img src="https://i.pinimg.com/736x/06/49/9b/06499b96d5228b6c7ac771e054bc2869.jpg"
                     alt="Ilustrasi Dokter Klinik Gen-Z"
                     class="rounded-4 shadow-lg max-w-full">
            </div>

            <!-- Text -->
            <div>
                <h1 class="text-4xl lg:text-5xl font-extrabold text-gray-900 leading-tight mb-4">
                    <span class="text-primary">Selamat Datang</span><br>di Klinik Gen-Z!
                </h1>
                <p class="text-gray-500 text-lg mb-8 leading-relaxed">
                    Daftarkan diri Anda secara online, pilih poliklinik,<br>
                    dan dapatkan nomor antrian kapan saja, di mana saja.<br>
                    Kesehatan Anda adalah prioritas kami.
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-4 mb-10">
                    @auth
                        @php
                            $dashboardRoute = match(auth()->user()->role) {
                                'admin' => route('admin.dashboard'),
                                'doctor' => route('doctor.dashboard'),
                                default => route('patient.dashboard'),
                            };
                        @endphp
                        <a href="{{ $dashboardRoute }}"
                           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-light text-white px-6 py-3 rounded-xl font-semibold transition shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-gauge"></i> Dashboard Saya
                        </a>
                    @else
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 bg-primary hover:bg-primary-light text-white px-6 py-3 rounded-xl font-semibold transition shadow-lg shadow-blue-200">
                            <i class="fa-solid fa-user-plus"></i> Daftar Sekarang
                        </a>
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center gap-2 border-2 border-primary text-primary hover:bg-primary-pale px-6 py-3 rounded-xl font-semibold transition">
                            <i class="fa-solid fa-right-to-bracket"></i> Masuk
                        </a>
                    @endauth
                </div>

                <!-- Stats -->
                <div class="flex gap-8 flex-wrap">
                    @php
                        $totalPatients = \App\Models\User::where('role', 'patient')->count();
                        $totalDoctors = \App\Models\User::where('role', 'doctor')->count();
                        $polies = \App\Models\Poly::where('is_active', true)->count();
                        $todayQueues = \App\Models\Queue::whereDate('queue_date', today())->count();
                    @endphp
                    <div><div class="text-2xl font-extrabold text-primary">{{ $totalPatients }}+</div><div class="text-xs text-gray-400">Pasien Terdaftar</div></div>
                    <div><div class="text-2xl font-extrabold text-primary">{{ $totalDoctors }}+</div><div class="text-xs text-gray-400">Dokter Aktif</div></div>
                    <div><div class="text-2xl font-extrabold text-primary">{{ $polies }}</div><div class="text-xs text-gray-400">Poliklinik</div></div>
                    <div><div class="text-2xl font-extrabold text-primary">{{ $todayQueues }}</div><div class="text-xs text-gray-400">Antrian Hari Ini</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURE STRIP -->
<section class="bg-white border-y border-blue-100 py-6">
    <div class="max-w-7xl mx-auto px-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-center">
            @foreach([
                ['fa-clock', 'Antrian Online', 'Daftar kapan & di mana saja'],
                ['fa-bell', 'Notifikasi WhatsApp', '10 menit sebelum giliran Anda'],
                ['fa-chart-bar', 'Survei Kepuasan', 'Bantu kami terus berkembang'],
                ['fa-shield-halved', 'Data Aman', 'Enkripsi password & privasi'],
            ] as [$icon, $title, $desc])
            <div class="p-4">
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fa-solid {{ $icon }} text-primary text-lg"></i>
                </div>
                <div class="font-semibold text-sm text-gray-800">{{ $title }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $desc }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- LAYANAN POLI -->
<section class="py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-extrabold text-gray-900 uppercase tracking-wide">Layanan Poli Klinik</h2>
            <p class="text-gray-400 mt-2">Tersedia poliklinik yang siap melayani kebutuhan kesehatan Anda</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @php
                $poliesList = \App\Models\Poly::where('is_active', true)->get();
            @endphp
            @foreach($poliesList as $p)
                @php
                    $waiting = \App\Models\Queue::where('poly_id', $p->id)
                        ->whereDate('queue_date', today())
                        ->whereIn('status', ['waiting', 'called'])
                        ->count();
                @endphp
                <div class="bg-white rounded-2xl border border-blue-100 shadow-sm hover:-translate-y-1 hover:shadow-md transition p-6 text-center flex flex-col">
                    <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mx-auto mb-4" style="background:{{ $p->color }}15">
                        <i class="fa-solid {{ $p->icon }} text-2xl" style="color:{{ $p->color }}"></i>
                    </div>
                    <h3 class="font-bold text-gray-900 text-base mb-1">{{ $p->name }}</h3>
                    <p class="text-gray-400 text-sm mb-3 flex-1">{{ $p->description }}</p>
                    <div class="flex items-center justify-center gap-1 text-xs text-gray-400 mb-4">
                        <i class="fa-solid fa-ticket"></i>
                        <span>{{ $waiting }} antrian aktif hari ini</span>
                    </div>
                    @auth
                        <a href="{{ route('patient.poli-info', ['id' => $p->id]) }}"
                           class="inline-flex items-center justify-center gap-1.5 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                            <i class="fa-solid fa-circle-info text-xs"></i>Informasi
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="inline-flex items-center justify-center gap-1.5 bg-primary text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-primary-light transition">
                            <i class="fa-solid fa-circle-info text-xs"></i>Informasi
                        </a>
                    @endauth
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-4">
        <div class="text-center mb-10">
            <h2 class="text-2xl font-extrabold text-gray-900">Cara Menggunakan</h2>
            <p class="text-gray-400 mt-2">Mudah, cepat, dan efisien dalam 4 langkah</p>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
            @foreach([
                ['1', 'fa-user-plus', 'Daftar Akun', 'Buat akun pasien Anda secara gratis'],
                ['2', 'fa-calendar-check', 'Pilih Poli & Jadwal', 'Pilih poliklinik dan jadwal dokter yang tersedia'],
                ['3', 'fa-ticket', 'Ambil Nomor Antrian', 'Dapatkan nomor antrian digital Anda'],
                ['4', 'fa-bell', 'Terima Notifikasi WA', 'Notif WhatsApp 10 menit sebelum dipanggil'],
            ] as [$n, $icon, $title, $desc])
            <div class="text-center">
                <div class="w-12 h-12 bg-primary text-white rounded-full flex items-center justify-center font-extrabold text-lg mx-auto mb-3">{{ $n }}</div>
                <i class="fa-solid {{ $icon }} text-primary text-2xl mb-2"></i>
                <h4 class="font-bold text-gray-800 mb-1">{{ $title }}</h4>
                <p class="text-sm text-gray-400">{{ $desc }}</p>
            </div>
            @endforeach
        </div>
        @guest
        <div class="text-center mt-10">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3 rounded-xl font-bold hover:bg-primary-light transition shadow-lg shadow-blue-200">
                Mulai Sekarang <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        @endguest
    </div>
</section>
@endsection