@extends('layouts.app')

@section('title', 'Daftar Antrian')

@section('content')
<div class="flex flex-1">
    @include('partials.sidebar-patient')

    <main class="flex-1 p-6 overflow-x-hidden">
        <h1 class="text-2xl font-extrabold text-gray-900 mb-1">Pendaftaran Antrian</h1>
        <p class="text-gray-400 text-sm mb-5">Pilih poli, dokter, dan tanggal kunjungan Anda</p>

        @php
            $user = auth()->user();
            $polies = \App\Models\Poly::where('is_active', true)->get();
            $doctors = \App\Models\Doctor::with(['user', 'poly'])
                ->where('is_available', true)
                ->get();
            $selectedDoctorId = request('doctor_id', 0);

            $activeQueue = \App\Models\Queue::where('patient_id', $user->id)
                ->whereDate('queue_date', today())
                ->whereNotIn('status', ['done', 'cancelled'])
                ->first();
        @endphp

        <!-- Success Modal -->
        <div id="successModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50 hidden" style="animation: fadeIn 0.3s ease-in;">
            <div class="bg-white rounded-3xl p-8 w-full max-w-md shadow-2xl" style="animation: slideUp 0.3s ease-out;">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-green-400 to-green-600 mb-4" style="animation: scaleIn 0.5s ease-out;">
                        <i class="fa-solid fa-check text-white text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-black text-gray-900">Berhasil Terdaftar!</h2>
                </div>

                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-6 mb-6 border border-gray-200">
                    <div class="text-center mb-5">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">NOMOR ANTRIAN ANDA</span>
                        <div class="text-5xl font-black text-primary mt-2" id="modalQueueNum">U-001</div>
                    </div>
                    <div class="border-t border-gray-300 pt-5 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Pasien:</span>
                            <span class="text-gray-900 font-medium" id="modalPatientName">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Poli:</span>
                            <span class="text-gray-900 font-medium" id="modalPoliName">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Tanggal:</span>
                            <span class="text-gray-900 font-medium" id="modalQueueDate">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="font-semibold text-gray-700">Est. Waktu:</span>
                            <span class="text-gray-900 font-medium" id="modalEstTime">-</span>
                        </div>
                    </div>
                </div>

                <button onclick="closeSuccessModal()" class="w-full bg-gradient-to-r from-primary to-blue-600 text-white font-bold py-3 px-4 rounded-2xl hover:shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-check-circle"></i>Selesai
                </button>
                <p class="text-xs text-gray-500 text-center mt-4">Notifikasi juga dikirim ke WhatsApp Anda</p>
            </div>
        </div>

        <style>
            @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
            @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
            @keyframes scaleIn { from { opacity: 0; transform: scale(0.5); } to { opacity: 1; transform: scale(1); } }
        </style>

        @if($activeQueue)
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-5">
                <div class="font-semibold text-blue-800 mb-1"><i class="fa-solid fa-info-circle mr-2"></i>Anda sudah memiliki antrian aktif hari ini</div>
                <div class="text-sm text-blue-600">Poli: {{ $activeQueue->poly->name }} — No. #{{ $activeQueue->queue_number }}</div>
                <a href="{{ route('queue.history') }}" class="inline-flex items-center gap-1 mt-3 text-sm text-blue-700 font-semibold underline">
                    Lihat antrian Anda →
                </a>
            </div>
        @endif

        @if(!$activeQueue)
        <div class="grid lg:grid-cols-5 gap-6">
            <!-- Form -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <h3 class="font-bold text-gray-800 mb-4"><i class="fa-solid fa-ticket mr-2 text-primary"></i>Isi Data Pendaftaran</h3>
                <form id="queueForm" class="space-y-4" onsubmit="submitQueueForm(event)">

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Poliklinik</label>
                        <select id="polySelect" onchange="filterDoctors()" class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Poli Terlebih Dahulu</option>
                            @foreach($polies as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Dokter</label>
                        <select id="doctorSelect" name="doctor_id" onchange="loadSchedules()" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Poli Dulu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Jadwal</label>
                        <select id="scheduleSelect" name="schedule_id" required
                                class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="">Pilih Dokter Dulu</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 mb-1">Tanggal Kunjungan</label>
                        <input type="date" name="queue_date" id="dateInput" required min="{{ date('Y-m-d') }}"
                               value="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <p class="text-xs text-gray-400 mt-1" id="dateHint"></p>
                    </div>

                    <div id="slotInfo" class="hidden bg-blue-50 rounded-xl p-3 text-xs text-blue-700"></div>

                    <button type="submit" id="submitBtn"
                            class="w-full bg-primary text-white py-3 rounded-xl font-bold text-sm hover:bg-primary-light transition flex items-center justify-center gap-2">
                        <i class="fa-solid fa-ticket"></i>Ambil Nomor Antrian
                    </button>
                </form>
            </div>

            <!-- Slot Info Panel -->
            <div class="lg:col-span-3 space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <h3 class="font-bold text-gray-800 mb-3"><i class="fa-solid fa-chart-bar mr-2 text-primary"></i>Antrian Hari Ini Per Poli</h3>
                    <div class="space-y-2">
                        @foreach($polies as $p)
                            @php
                                $cnt = \App\Models\Queue::where('poly_id', $p->id)->whereDate('queue_date', today())->whereNotIn('status', ['cancelled'])->count();
                                $maxSl = \App\Models\Schedule::whereHas('doctor', function($q) use ($p) {
                                    $q->where('poly_id', $p->id);
                                })->where('day_of_week', date('w'))->where('is_available', 1)->sum('max_slots');
                                $pct = $maxSl > 0 ? min(100, round($cnt / $maxSl * 100)) : 0;
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs flex-shrink-0" style="background:{{ $p->color }}">
                                    <i class="fa-solid {{ $p->icon }}"></i>
                                </span>
                                <span class="w-24 text-xs font-medium text-gray-600">{{ $p->name }}</span>
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="h-2 rounded-full transition-all" style="width:{{ $pct }}%;background:{{ $p->color }}"></div>
                                </div>
                                <span class="text-xs text-gray-500 w-16 text-right">{{ $cnt }}/{{ $maxSl ?: '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-sm">
                    <h4 class="font-bold text-amber-800 mb-2"><i class="fa-solid fa-triangle-exclamation mr-1"></i>Informasi Penting</h4>
                    <ul class="text-amber-700 text-xs space-y-1 list-disc list-inside">
                        <li>Hadir <strong>10 menit</strong> sebelum estimasi giliran Anda.</li>
                        <li>Notifikasi WhatsApp dikirim 10 menit sebelum dipanggil.</li>
                        <li>Jika terlambat &amp; poli ramai, antrian otomatis <strong>hangus</strong>.</li>
                        <li>Jika terlambat &amp; poli tidak ramai, antrian <strong>digeser</strong> ke slot terakhir.</li>
                        <li>Satu pasien hanya bisa memiliki 1 antrian aktif per hari.</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </main>
</div>

<script>
const allDoctors = @json($doctors);
const DAYS_ID = @json(config('klinik.days_id'));
const APP_URL = '{{ config('app.url') }}';
const API_BASE = '{{ config('app.url') }}';

function filterDoctors() {
    const polyId = parseInt(document.getElementById('polySelect').value);
    const sel = document.getElementById('doctorSelect');
    sel.innerHTML = '<option value="">Pilih Dokter</option>';
    document.getElementById('scheduleSelect').innerHTML = '<option value="">Pilih Dokter Dulu</option>';
    allDoctors.filter(d => d.poly_id == polyId).forEach(d => {
        sel.innerHTML += `<option value="${d.id}">dr. ${d.user.username}</option>`;
    });
    @if($selectedDoctorId)
    sel.value = '{{ $selectedDoctorId }}';
    if (sel.value) loadSchedules();
    @endif
}

async function loadSchedules() {
    const did = document.getElementById('doctorSelect').value;
    const sel = document.getElementById('scheduleSelect');
    sel.innerHTML = '<option value="">Memuat jadwal...</option>';
    if (!did) { sel.innerHTML = '<option value="">Pilih Dokter Dulu</option>'; return; }

    try {
        const response = await fetch(`/api/schedule/by-doctor?doctor_id=${did}`);
        const data = await response.json();
        sel.innerHTML = '<option value="">Pilih Jadwal</option>';
        if (!data.success || !data.schedules.length) {
            sel.innerHTML = '<option value="">Tidak ada jadwal tersedia</option>';
            return;
        }
        data.schedules.forEach(s => {
            sel.innerHTML += `<option value="${s.id}" data-day="${s.day_of_week}">${DAYS_ID[s.day_of_week]} | ${s.start_time.slice(0,5)}–${s.end_time.slice(0,5)} (${s.slots_left} slot tersisa)</option>`;
        });
        sel.onchange = updateDateFromSchedule;
    } catch (error) {
        console.error('Gagal memuat jadwal:', error);
        sel.innerHTML = '<option value="">Gagal memuat jadwal. Segarkan halaman.</option>';
    }
}

function updateDateFromSchedule() {
    const opt = document.getElementById('scheduleSelect').selectedOptions[0];
    if (!opt || !opt.dataset.day) return;
    const day = parseInt(opt.dataset.day);
    const today = new Date();
    const diff = (day - today.getDay() + 7) % 7 || 7;
    const next = new Date(today.getTime() + diff * 86400000);
    const ds = next.toISOString().split('T')[0];
    document.getElementById('dateInput').value = ds;
    document.getElementById('dateHint').textContent = 'Tanggal otomatis disesuaikan dengan hari jadwal.';
}

// Pre-select poli jika ada parameter
@if($selectedDoctorId)
const preDoc = allDoctors.find(d => d.id == {{ $selectedDoctorId }});
if (preDoc) {
    document.getElementById('polySelect').value = preDoc.poly_id;
    filterDoctors();
}
@endif

// Submit form via AJAX
async function submitQueueForm(e) {
    e.preventDefault();
    const form = document.getElementById('queueForm');
    const submitBtn = document.getElementById('submitBtn');
    const formData = new FormData(form);

    submitBtn.disabled = true;
    const originalHtml = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner animate-spin mr-2"></i>Memproses...';

    try {
        const response = await fetch('/api/queue-register', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            showSuccessModal(data);
        } else {
            alert('Pendaftaran gagal: ' + (data.message || 'Terjadi kesalahan'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHtml;
    }
}

function showSuccessModal(data) {
    document.getElementById('modalQueueNum').textContent = 'U-' + String(data.queue_number).padStart(3, '0');
    document.getElementById('modalPatientName').textContent = data.patient_name;
    document.getElementById('modalPoliName').textContent = data.poly_name;
    document.getElementById('modalQueueDate').textContent = new Date(data.queue_date + 'T00:00:00').toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
    document.getElementById('modalEstTime').textContent = data.estimated_time;
    document.getElementById('successModal').classList.remove('hidden');
}

function closeSuccessModal() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('queueForm').reset();
    document.getElementById('polySelect').value = '';
    document.getElementById('doctorSelect').innerHTML = '<option value="">Pilih Poli Dulu</option>';
    document.getElementById('scheduleSelect').innerHTML = '<option value="">Pilih Dokter Dulu</option>';
    document.getElementById('dateInput').value = '{{ date('Y-m-d') }}';
    document.getElementById('submitBtn').disabled = false;
    document.getElementById('submitBtn').innerHTML = '<i class="fa-solid fa-ticket"></i>Ambil Nomor Antrian';
    setTimeout(() => location.reload(), 1000);
}
</script>
@endsection