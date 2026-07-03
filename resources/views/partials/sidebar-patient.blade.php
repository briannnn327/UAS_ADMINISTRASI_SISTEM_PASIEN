@php
    $user = auth()->user();
@endphp

<aside class="hidden md:flex flex-col w-64 min-h-screen bg-gradient-to-b from-primary-dark to-primary shrink-0">
    <div class="p-4 m-3 bg-white/10 rounded-2xl text-center">
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-extrabold text-xl mx-auto mb-2">
            {{ strtoupper(substr($user->username, 0, 1)) }}
        </div>
        <div class="text-white font-semibold text-sm">{{ $user->username }}</div>
        <div class="text-white/60 text-xs uppercase tracking-wider mt-0.5">Pasien</div>
    </div>

    <nav class="px-3 pb-4 flex flex-col gap-1">
        @php
            $links = [
                ['route' => 'patient.dashboard', 'icon' => 'fa-gauge', 'label' => 'Dashboard'],
                ['route' => 'patient.poli-info', 'icon' => 'fa-hospital', 'label' => 'Info Poli & Jadwal'],
                ['route' => 'patient.register-queue', 'icon' => 'fa-ticket', 'label' => 'Daftar Antrian'],
                ['route' => 'patient.survey-chart', 'icon' => 'fa-chart-bar', 'label' => 'Grafik Survei'],
            ];
        @endphp
        @foreach($links as $link)
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ Route::currentRouteName() === $link['route'] ? 'bg-white/20 text-white' : 'text-white/75 hover:bg-white/15 hover:text-white' }}">
                <i class="fa-solid {{ $link['icon'] }} w-4 text-center"></i>
                {{ $link['label'] }}
            </a>
        @endforeach

        <hr class="border-white/20 my-1">
        <a href="{{ route('queue.history') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-white/75 hover:bg-white/15 transition">
            <i class="fa-solid fa-clock-rotate-left w-4 text-center"></i>Riwayat Antrian
        </a>

        <hr class="border-white/20 my-1">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-red-300 hover:bg-red-500/20 hover:text-red-200 transition text-left">
                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>Logout
            </button>
        </form>
    </nav>
</aside>