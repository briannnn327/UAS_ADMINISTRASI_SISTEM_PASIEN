@extends('layouts.app')

@section('title', 'Terima Kasih!')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-green-50 to-teal-50 flex items-center justify-center py-10 px-4">
    <div class="max-w-md w-full">

        <div class="bg-white rounded-3xl shadow-xl p-8 text-center mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-green-400 to-teal-500 rounded-full flex items-center justify-center mx-auto mb-5 shadow-lg shadow-green-200">
                <i class="fa-solid fa-heart text-white text-3xl"></i>
            </div>

            <h1 class="text-2xl font-extrabold text-gray-900 mb-2">Terima Kasih, {{ $name ?? 'Pasien' }}!</h1>
            <p class="text-gray-500 text-sm mb-6 leading-relaxed">
                Survei kepuasan Anda telah berhasil dikirim.<br>
                Penilaian Anda sangat membantu kami meningkatkan kualitas layanan.
            </p>

            <div class="flex justify-center gap-1 mb-6">
                @for($i=0; $i<5; $i++)
                    <i class="fa-solid fa-star text-yellow-400 text-2xl" style="animation:bounce .6s ease {{ $i*.1 }}s infinite alternate"></i>
                @endfor
            </div>

            @php
                $avg = \App\Models\Survey::avg('overall_rating');
                $total = \App\Models\Survey::count();
            @endphp
            <div class="bg-gray-50 rounded-2xl p-4 mb-6">
                <div class="text-3xl font-extrabold text-primary">{{ $avg ? round($avg, 1) : '—' }}<span class="text-base text-gray-400">/5</span></div>
                <div class="text-xs text-gray-500 mt-1">Rata-rata kepuasan dari {{ $total }} responden</div>
                <div class="flex justify-center gap-1 mt-2">
                    @for($i=1; $i<=5; $i++)
                        <i class="fa-{{ $i <= round($avg ?? 0) ? 'solid' : 'regular' }} fa-star text-yellow-400 text-sm"></i>
                    @endfor
                </div>
            </div>

            <div class="flex flex-col gap-3">
                @auth
                    <a href="{{ route('patient.dashboard') }}"
                       class="bg-primary text-white py-3 rounded-xl font-bold text-sm hover:bg-primary-light transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-gauge"></i>Kembali ke Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="bg-primary text-white py-3 rounded-xl font-bold text-sm hover:bg-primary-light transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-right-to-bracket"></i>Login
                    </a>
                @endauth
            </div>
        </div>

        <p class="text-center text-xs text-gray-400">
            <i class="fa-solid fa-shield-halved mr-1"></i>
            Data survei Anda tersimpan aman dan hanya digunakan untuk peningkatan layanan.
        </p>
    </div>
</div>

<style>
@keyframes bounce{from{transform:translateY(0)}to{transform:translateY(-8px)}}
</style>
@endsection