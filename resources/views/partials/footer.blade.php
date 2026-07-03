<footer class="mt-auto bg-gradient-to-br from-primary-dark to-primary py-6 text-white">
    <div class="max-w-7xl mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-3 text-sm text-white/80">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center text-white text-xs">
                <i class="fa-solid fa-hospital-user"></i>
            </div>
            <span class="font-semibold text-white">{{ config('app.name') }}</span>
            <span class="text-white/70">&bull;</span>
            <span class="text-white/70">Sistem Administrasi & Manajemen Pasien</span>
        </div>
        <div>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</div>
        <div class="flex gap-4">
            <a href="{{ route('landing') }}" class="text-white font-medium hover:text-white/90">Beranda</a>
            <a href="{{ route('login') }}" class="text-white font-medium hover:text-white/90">Login</a>
            <a href="{{ route('register') }}" class="text-white font-semibold hover:text-white/90">Daftar</a>
        </div>
    </div>
</footer>