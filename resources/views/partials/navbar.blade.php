<nav class="bg-white border-b-2 border-blue-50 shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            <!-- Brand -->
            <a href="{{ route('landing') }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white text-lg">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <div>
                    <div class="font-bold text-primary leading-tight text-sm">{{ config('app.name') }}</div>
                    <div class="text-gray-400 text-xs leading-tight">Klinik Digital</div>
                </div>
            </a>

            <!-- Right side -->
            <div class="flex items-center gap-2">
                @auth
                    <!-- Notification Bell -->
                    <div class="relative" id="notif-wrapper">
                        <button onclick="toggleNotifPanel()" class="relative p-2 rounded-lg hover:bg-blue-50 text-gray-500">
                            <i class="fa-regular fa-bell text-lg"></i>
                            @php
                                $unread = app(\App\Services\NotificationService::class)->unreadCount(auth()->id());
                            @endphp
                            @if($unread > 0)
                                <span id="notif-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                                    {{ min($unread, 99) }}
                                </span>
                            @else
                                <span id="notif-badge" class="hidden"></span>
                            @endif
                        </button>
                        <!-- Dropdown -->
                        <div id="notif-panel" class="hidden absolute right-0 top-12 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <span class="font-semibold text-sm">Notifikasi</span>
                                <button onclick="markAllRead()" class="text-primary text-xs font-medium">Tandai semua dibaca</button>
                            </div>
                            <div id="notif-list" class="max-h-72 overflow-y-auto">
                                <div class="text-center text-gray-400 py-6 text-sm">Memuat...</div>
                            </div>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 px-3 py-1.5 rounded-xl hover:bg-blue-50">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-primary to-accent flex items-center justify-center text-white font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->username, 0, 1)) }}
                            </div>
                            <span class="hidden md:block text-sm font-medium">{{ auth()->user()->username }}</span>
                            <i class="fa-solid fa-chevron-down text-xs text-gray-400"></i>
                        </button>
                        <div class="hidden group-hover:block absolute right-0 top-10 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
                            @php
                                $dashboardRoute = match(auth()->user()->role) {
                                    'admin' => route('admin.dashboard'),
                                    'doctor' => route('doctor.dashboard'),
                                    default => route('patient.dashboard'),
                                };
                            @endphp
                            <a href="{{ $dashboardRoute }}" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-blue-50 text-gray-700">
                                <i class="fa-solid fa-gauge text-primary w-4"></i>Dashboard
                            </a>
                            <hr class="my-1 border-gray-100">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-red-50 text-red-500 w-full text-left">
                                    <i class="fa-solid fa-right-from-bracket w-4"></i>Logout
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('register') }}" class="hidden sm:flex items-center gap-1.5 px-4 py-2 rounded-xl border border-primary text-primary text-sm font-semibold hover:bg-primary-pale transition">
                        <i class="fa-solid fa-user-plus text-xs"></i>Register
                    </a>
                    <a href="{{ route('login') }}" class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-primary text-white text-sm font-semibold hover:bg-primary-light transition">
                        <i class="fa-solid fa-right-to-bracket text-xs"></i>Login
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>