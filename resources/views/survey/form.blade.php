@extends('layouts.app')

@section('title', 'Survei Kepuasan')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-50 py-10 px-4">
    <div class="max-w-xl mx-auto">

        <!-- Header card -->
        <div class="rounded-2xl p-6 mb-6 text-white shadow-lg"
             style="background:linear-gradient(135deg, {{ $queue->poly->color }}, {{ $queue->poly->color }}aa)">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-2xl">
                    <i class="fa-solid {{ $queue->poly->icon }}"></i>
                </div>
                <div>
                    <p class="text-xs opacity-75 mb-0.5">Survei Kepuasan Pasien</p>
                    <h1 class="text-xl font-extrabold">{{ $queue->poly->name }}</h1>
                    <p class="text-sm opacity-80">Halo, <strong>{{ $queue->patient->username }}</strong>! Tanggal {{ date('d M Y', strtotime($queue->queue_date)) }}</p>
                </div>
            </div>
        </div>

        <!-- Survey form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="font-extrabold text-gray-800 text-lg mb-1">Bagaimana pengalaman Anda?</h2>
            <p class="text-gray-400 text-sm mb-6">Berikan penilaian jujur Anda. Hanya butuh 1 menit!</p>

            <form method="POST" action="{{ route('survey.store') }}">
                @csrf
                <input type="hidden" name="queue_id" value="{{ $queue->id }}">
                <input type="hidden" name="patient_id" value="{{ $queue->patient->id }}">

                @php
                    $aspects = [
                        ['doctor_rating', 'fa-user-doctor', 'Pelayanan Dokter', 'Bagaimana pelayanan dr. '.$queue->doctor->user->username.'?'],
                        ['service_rating', 'fa-hand-holding-heart', 'Pelayanan Staf', 'Bagaimana keramahan dan kecepatan staf?'],
                        ['facility_rating', 'fa-building', 'Fasilitas Klinik', 'Bagaimana kebersihan dan kenyamanan fasilitas?'],
                        ['overall_rating', 'fa-star', 'Kesan Keseluruhan', 'Secara keseluruhan, seberapa puas Anda?'],
                    ];
                @endphp

                @foreach($aspects as [$name, $icon, $label, $desc])
                <div class="mb-6 p-4 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="fa-solid {{ $icon }} text-primary text-sm"></i>
                        <span class="font-semibold text-gray-800 text-sm">{{ $label }}</span>
                    </div>
                    <p class="text-xs text-gray-400 mb-3">{{ $desc }}</p>
                    <div class="star-rating flex-row-reverse justify-end flex">
                        @for ($s = 5; $s >= 1; $s--)
                            <input type="radio" name="{{ $name }}" id="{{ $name }}-{{ $s }}" value="{{ $s }}" {{ $s===5 ? 'required' : '' }}>
                            <label for="{{ $name }}-{{ $s }}" title="{{ $s }} bintang" class="text-3xl cursor-pointer px-0.5 text-gray-200 hover:text-yellow-400 transition-colors">★</label>
                        @endfor
                    </div>
                    <p class="text-xs text-gray-400 mt-1" id="hint-{{ $name }}">Klik bintang untuk memberi nilai</p>
                </div>
                @endforeach

                <!-- Comments -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fa-solid fa-comment mr-1 text-primary"></i>Komentar / Saran (opsional)
                    </label>
                    <textarea name="comments" rows="3" placeholder="Tuliskan saran atau masukan Anda untuk klinik kami..."
                              class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-primary resize-none"></textarea>
                </div>

                <button type="submit"
                        class="w-full bg-gradient-to-r from-primary to-primary-light text-white py-3.5 rounded-xl font-bold text-sm hover:opacity-90 transition shadow-lg shadow-blue-200 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane"></i>Kirim Survei
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-gray-400 mt-4">
            <i class="fa-solid fa-shield-halved mr-1"></i>
            Data survei Anda bersifat rahasia dan hanya digunakan untuk meningkatkan kualitas layanan.
        </p>
    </div>
</div>

<script>
document.querySelectorAll('.star-rating input').forEach(radio => {
    radio.addEventListener('change', function() {
        const name = this.name;
        const hint = document.getElementById('hint-' + name);
        const labels = ['', 'Sangat Tidak Puas', 'Tidak Puas', 'Cukup Puas', 'Puas', 'Sangat Puas'];
        if (hint) hint.textContent = labels[parseInt(this.value)] || '';
    });
});
</script>

<style>
.star-rating input { display:none; }
.star-rating label { font-size:1.8rem; color:#d1d5db; cursor:pointer; transition:color .15s; }
.star-rating input:checked ~ label,
.star-rating label:hover,
.star-rating label:hover ~ label { color:#f59e0b; }
</style>
@endsection