@php
    $user = auth()->user();
    $currentRoute = Route::currentRouteName();
@endphp

<aside class="hidden md:flex flex-col w-64 min-h-screen bg-gradient-to-b from-primary-dark to-primary shrink-0">
    <!-- User card -->
    <div class="p-4 m-3 bg-white/10 rounded-2xl text-center">
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-white font-extrabold text-xl mx-auto mb-2">
            {{ strtoupper(substr($user->username, 0, 1)) }}
        </div>
        <div class="text-white font-semibold text-sm">{{ $user->username }}</div>
        <div class="text-white/60 text-xs uppercase tracking-wider mt-0.5">Administrator</div>
    </div>

    <!-- Links -->
    <nav class="px-3 pb-4 flex flex-col gap-1">
        @php
            $links = [
                ['route' => 'admin.dashboard', 'icon' => 'fa-gauge', 'label' => 'Dashboard'],
                ['route' => 'admin.users.index', 'icon' => 'fa-users', 'label' => 'Pengguna'],
                ['route' => 'admin.doctors.index', 'icon' => 'fa-user-doctor', 'label' => 'Dokter'],
                ['route' => 'admin.schedules.index', 'icon' => 'fa-calendar-days', 'label' => 'Jadwal Praktik'],
                ['route' => 'admin.queues.index', 'icon' => 'fa-ticket', 'label' => 'Antrian'],
                ['route' => 'admin.reports.index', 'icon' => 'fa-chart-bar', 'label' => 'Laporan & Survei'],
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

        <hr class="border-white/20 my-2">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium text-red-300 hover:bg-red-500/20 hover:text-red-200 transition text-left">
                <i class="fa-solid fa-right-from-bracket w-4 text-center"></i>Logout
            </button>
        </form>
    </nav>
</aside>