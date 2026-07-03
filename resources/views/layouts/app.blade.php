<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name')) — {{ config('app.name') }}</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { DEFAULT: '#1565C0', light: '#1976D2', pale: '#E3F2FD', dark: '#0D47A1' },
                        accent: { DEFAULT: '#00ACC1', light: '#E0F7FA' },
                    },
                    fontFamily: { sans: ['Poppins', 'ui-sans-serif', 'system-ui'] },
                }
            }
        }
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" defer></script>

    <style>
        .sidebar-link.active { background: rgba(255,255,255,.2); color: #fff; }
        .toast { animation: slideIn .3s ease; }
        @keyframes slideIn { from { opacity:0; transform:translateX(40px) } to { opacity:1; transform:translateX(0) } }
        .star-rating input { display:none; }
        .star-rating label { font-size:1.8rem; color:#d1d5db; cursor:pointer; transition:color .15s; }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label { color:#f59e0b; }
        ::-webkit-scrollbar { width:6px; }
        ::-webkit-scrollbar-thumb { background:#1565C0; border-radius:3px; }
    </style>

    @stack('styles')
</head>
<body class="bg-blue-50 font-sans text-gray-800 flex flex-col min-h-screen">

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>

    <!-- Navbar -->
    @include('partials.navbar')

    <!-- Flash Message -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-green-400 bg-green-50 text-green-800 text-sm" id="flash-msg">
                <i class="fa-solid fa-circle-check"></i>
                {{ session('success') }}
                <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-lg leading-none">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border border-red-400 bg-red-50 text-red-800 text-sm" id="flash-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ session('error') }}
                <button onclick="document.getElementById('flash-msg').remove()" class="ml-auto text-lg leading-none">&times;</button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    {{ $slot ?? '' }}
    @yield('content')

    <!-- Footer -->
    @include('partials.footer')

    <!-- Scripts -->
    <script>
        // Global variables
        const APP_USER = @json(auth()->user() ? ['id' => auth()->id(), 'role' => auth()->user()->role] : null);
        const APP_URL = '{{ config('app.url') }}';

        // Toast helper
        function showToast(title, msg, type = 'info') {
            const colors = { info:'border-blue-400', success:'border-green-400', warning:'border-yellow-400', danger:'border-red-400' };
            const icons  = { info:'circle-info', success:'circle-check', warning:'triangle-exclamation', danger:'circle-exclamation' };
            const el = document.createElement('div');
            el.className = `toast bg-white rounded-xl shadow-xl border-l-4 ${colors[type]||colors.info} px-4 py-3 w-72`;
            el.innerHTML = `
                <div class="flex items-start gap-2">
                    <i class="fa-solid fa-${icons[type]||icons.info} mt-0.5 text-${type==='success'?'green':type==='danger'?'red':type==='warning'?'yellow':'blue'}-500"></i>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-gray-800">${title}</p>
                        <p class="text-xs text-gray-500 mt-0.5">${msg}</p>
                    </div>
                    <button onclick="this.closest('.toast').remove()" class="text-gray-300 hover:text-gray-500 text-lg leading-none">&times;</button>
                </div>`;
            document.getElementById('toast-container').appendChild(el);
            setTimeout(() => el.remove(), 6000);
        }

        // Notification panel
        function toggleNotifPanel() {
            const panel = document.getElementById('notif-panel');
            if (!panel) return;
            panel.classList.toggle('hidden');
            if (!panel.classList.contains('hidden')) fetchNotifications();
        }

        async function fetchNotifications() {
            if (!APP_USER) return;
            try {
                const r = await fetch('/notification/list');
                const d = await r.json();
                if (!d.success) return;
                const list = document.getElementById('notif-list');
                const badge = document.getElementById('notif-badge');
                if (badge) {
                    if (d.unread > 0) {
                        badge.textContent = Math.min(d.unread, 99);
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
                if (list) {
                    list.innerHTML = d.notifications.length
                        ? d.notifications.slice(0,10).map(n => `
                            <div class="px-4 py-3 border-b border-gray-50 ${n.is_read?'':'bg-blue-50'} hover:bg-gray-50">
                                <p class="font-medium text-xs text-gray-800">${n.title}</p>
                                <p class="text-xs text-gray-500">${n.message}</p>
                                <p class="text-xs text-gray-300 mt-1">${n.created_at}</p>
                            </div>`).join('')
                        : '<p class="text-center text-gray-400 py-6 text-xs">Tidak ada notifikasi</p>';
                }
            } catch(e) {}
        }

        async function markAllRead() {
            await fetch('/api/notification/mark-read', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            });
            document.getElementById('notif-badge')?.classList.add('hidden');
            const list = document.getElementById('notif-list');
            if (list) list.innerHTML = '<p class="text-center text-gray-400 py-6 text-xs">Semua telah dibaca</p>';
        }

        // Real-time queue polling
        function startQueuePoll(queueId) {
            const badge = document.getElementById('q-status-badge');
            const posEl = document.getElementById('q-position');
            if (!badge) return;
            const poll = async () => {
                try {
                    const d = await (await fetch(`/api/queue/status?id=${queueId}`)).json();
                    if (!d.success) return;
                    badge.textContent = d.status_label;
                    badge.className = `px-3 py-1 rounded-full text-sm font-semibold ${d.status_tw}`;
                    if (posEl) posEl.textContent = d.position ?? '—';
                    if (d.status === 'done') {
                        clearInterval(iv);
                        showToast('Pemeriksaan Selesai!', 'Terima kasih. Mohon isi survei kepuasan Anda.', 'success');
                        if (d.survey_url) setTimeout(() => { window.location.href = d.survey_url; }, 3500);
                    }
                } catch(e) {}
            };
            poll();
            const iv = setInterval(poll, 15000);
        }

        // Notification polling
        if (APP_USER) {
            setInterval(fetchNotifications, 30000);
        }

        // Auto-dismiss flash
        setTimeout(() => document.getElementById('flash-msg')?.remove(), 5000);
    </script>

    @stack('scripts')
</body>
</html>