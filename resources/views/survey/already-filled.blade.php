@extends('layouts.app')

@section('title', 'Survei Sudah Diisi')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 flex items-center justify-center py-10 px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center max-w-md mx-auto">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fa-solid fa-circle-check text-green-500 text-3xl"></i>
        </div>
        <h2 class="text-xl font-extrabold text-gray-800 mb-2">Survei Sudah Diisi!</h2>
        <p class="text-gray-400 text-sm mb-5">Terima kasih telah memberikan penilaian Anda.</p>
        <a href="{{ route('patient.dashboard') }}"
           class="inline-flex items-center gap-2 bg-primary text-white px-6 py-2.5 rounded-xl font-semibold text-sm hover:bg-primary-light transition">
            <i class="fa-solid fa-gauge"></i>Kembali ke Dashboard
        </a>
    </div>
</div>
@endsection