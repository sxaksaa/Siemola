<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} | {{ $title ?? 'SIEMOLA' }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="siemola-dashboard min-h-screen">
        @php
            $iconMap = [
                'dashboard' => 'M4 5a1 1 0 0 1 1-1h5v6H4V5Zm10-1h5a1 1 0 0 1 1 1v5h-6V4ZM4 14h6v6H5a1 1 0 0 1-1-1v-5Zm10 0h6v5a1 1 0 0 1-1 1h-5v-6Z',
                'staff' => 'M5 18h14M6 18v-1a4 4 0 0 1 4-4h0a4 4 0 0 1 4 4v1M12 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-6 4a3 3 0 0 0-3 3v1m18-1v-1a3 3 0 0 0-3-3m-1-7a3 3 0 1 1-6 0a3 3 0 0 1 6 0Z',
                'students' => 'M16 21v-1.2a3.8 3.8 0 0 0-3.8-3.8H8.8A3.8 3.8 0 0 0 5 19.8V21m11-11a3 3 0 1 1-6 0a3 3 0 0 1 6 0Zm4 8.2V18a3.2 3.2 0 0 0-2.5-3.1m-1.3-7.4a2.8 2.8 0 1 1 0 5.6',
                'locker' => 'M8 11V8a4 4 0 1 1 8 0v3m-9 0h10a1 1 0 0 1 1 1v7a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2v-7a1 1 0 0 1 1-1Z',
                'history' => 'M3 12a9 9 0 1 0 3-6.7M3 4v5h5m4-1v5l3 2',
                'bell' => 'M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.42V11a6 6 0 1 0-12 0v3.18a2 2 0 0 1-.6 1.41L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9',
                'user' => 'M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0',
            ];

            $user = auth()->user();
            $displayTimezone = config('app.display_timezone', 'Asia/Jakarta');
            $menu = collect([
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'roles' => ['guest', 'staff', 'admin']],
                ['label' => 'Histori Peminjaman', 'route' => 'history.index', 'icon' => 'history', 'roles' => ['staff']],
                ['label' => 'Data Mahasiswa', 'route' => 'students.index', 'icon' => 'students', 'roles' => ['admin']],
                ['label' => 'Data Staf', 'route' => 'staff.index', 'icon' => 'staff', 'roles' => ['admin']],
                ['label' => 'Data Loker', 'route' => 'lockers.index', 'icon' => 'locker', 'roles' => ['admin']],
            ])->filter(fn ($item) => in_array($user?->role ?? 'guest', $item['roles'], true));
        @endphp

        <div class="siemola-layout">
            <aside class="siemola-sidebar">
                <div class="siemola-brand">
                    <div class="siemola-brand-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-8 w-8">
                            <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z" />
                            <path d="M12 3v18M4 7l8 4 8-4M8 5l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="siemola-brand-title">SIEMOLA</p>
                        <p class="siemola-brand-subtitle">Smart Locker System</p>
                    </div>
                </div>

                <nav class="siemola-side-nav">
                    @foreach ($menu as $item)
                        @php
                            $isActive = ($activeMenu ?? '') === $item['label'];
                            $href = $item['route'] ? route($item['route']) : '#';
                        @endphp

                        <a href="{{ $href }}" class="siemola-nav-item {{ $isActive ? 'siemola-nav-item-active' : '' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                <path d="{{ $iconMap[$item['icon']] }}" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

            </aside>

            <div class="siemola-main-shell">
                <header class="siemola-topbar">
                    <div class="siemola-topbar-inner">
                        <div>
                            <h1 class="siemola-page-title">{{ $title ?? 'Dashboard' }}</h1>
                        </div>

                        <div class="siemola-topbar-actions">
                            @if ($user?->role === 'staff')
                                <x-dropdown align="right" width="80" contentClasses="siemola-dropdown-surface">
                                    <x-slot name="trigger">
                                        <button type="button" class="siemola-icon-button">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                                <path d="{{ $iconMap['bell'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            @if (($staffNotificationCount ?? 0) > 0)
                                                <span class="siemola-notification-dot"></span>
                                            @endif
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="siemola-notification-menu">
                                            <div class="siemola-dropdown-header">
                                                <p class="siemola-dropdown-title">Notifikasi Keterlambatan</p>
                                                <p class="siemola-dropdown-muted">{{ ($staffNotificationCount ?? 0) > 0 ? ($staffNotificationCount.' transaksi terlambat') : 'Tidak ada keterlambatan aktif' }}</p>
                                            </div>

                                            <div class="siemola-notification-list">
                                                @forelse (($staffNotifications ?? collect()) as $notification)
                                                    <a href="{{ route('history.index', ['search' => $notification->student?->name]) }}" class="siemola-notification-item">
                                                        <p class="siemola-notification-name">{{ $notification->student?->name ?? 'Mahasiswa' }}</p>
                                                        <p class="siemola-notification-text">
                                                            {{ $notification->locker?->code ?? 'Loker' }} terlambat sejak {{ $notification->due_at?->copy()->timezone($displayTimezone)->format('d/m/Y H:i') ?? '-' }}.
                                                        </p>
                                                    </a>
                                                @empty
                                                    <div class="siemola-empty-message">
                                                        Semua peminjaman masih aman.
                                                    </div>
                                                @endforelse
                                            </div>

                                            @if (($staffNotificationCount ?? 0) > 5)
                                                <a href="{{ route('history.index') }}" class="siemola-dropdown-footer-link">Lihat semua histori</a>
                                            @endif
                                        </div>
                                    </x-slot>
                                </x-dropdown>
                            @endif

                            @auth
                                <x-dropdown align="right" width="64" contentClasses="siemola-dropdown-surface">
                                    <x-slot name="trigger">
                                        <button type="button" class="siemola-user-trigger">
                                            <div class="siemola-user-avatar">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                                    <path d="{{ $iconMap['user'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <p class="siemola-user-name">{{ $user->name }}</p>
                                                <p class="siemola-user-role">{{ $userRole ?? 'Admin' }}</p>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="space-y-1">
                                            <a href="{{ route('profile.edit') }}" class="siemola-dropdown-link">Profile</a>

                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="siemola-dropdown-link siemola-dropdown-danger">Logout</button>
                                            </form>
                                        </div>
                                    </x-slot>
                                </x-dropdown>
                            @endauth

                            @guest
                                <div class="flex items-center gap-3">
                                    <div class="siemola-guest-user">
                                        <p class="siemola-user-name">Mahasiswa</p>
                                        <p class="siemola-user-role">Dashboard Publik</p>
                                    </div>

                                    <a href="{{ route('login') }}" class="siemola-primary-button px-6 py-3">
                                        Login Staff/Admin
                                    </a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </header>

                <main class="siemola-main-content" data-siemola-refresh-target="main">
                    @if (session('status'))
                        <div class="siemola-flash">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        @if ($autoRefresh)
            <script>
                (() => {
                    const interval = Number(@json($autoRefreshInterval));
                    const refreshTargetSelector = '[data-siemola-refresh-target="main"]';
                    let inFlight = false;
                    let filterTimer = null;

                    const isInteractiveElement = (element) => {
                        if (!element) return false;

                        return ['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(element.tagName)
                            || element.isContentEditable
                            || Boolean(element.closest('form'));
                    };

                    const shouldPauseRefresh = () => {
                        if (document.hidden || inFlight) return true;

                        const activeElement = document.activeElement;

                        return isInteractiveElement(activeElement);
                    };

                    const refreshPage = async () => {
                        if (shouldPauseRefresh()) return;

                        inFlight = true;

                        try {
                            const response = await fetch(window.location.href, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-SIEMOLA-AUTO-REFRESH': '1',
                                },
                                credentials: 'same-origin',
                            });

                            if (!response.ok || response.redirected) return;

                            const html = await response.text();
                            const nextDocument = new DOMParser().parseFromString(html, 'text/html');
                            const currentMain = document.querySelector(refreshTargetSelector);
                            const nextMain = nextDocument.querySelector(refreshTargetSelector);
                            const currentTopbarActions = document.querySelector('.siemola-topbar-actions');
                            const nextTopbarActions = nextDocument.querySelector('.siemola-topbar-actions');

                            if (currentMain && nextMain) {
                                const scrollTop = window.scrollY;
                                currentMain.innerHTML = nextMain.innerHTML;
                                window.Alpine?.initTree(currentMain);
                                window.scrollTo({ top: scrollTop });
                            }

                            if (currentTopbarActions && nextTopbarActions) {
                                currentTopbarActions.innerHTML = nextTopbarActions.innerHTML;
                                window.Alpine?.initTree(currentTopbarActions);
                            }
                        } catch (error) {
                            console.debug('SIEMOLA auto refresh skipped:', error);
                        } finally {
                            inFlight = false;
                        }
                    };

                    const submitFilter = (form) => {
                        if (!form) return;

                        if (form.requestSubmit) {
                            form.requestSubmit();
                            return;
                        }

                        form.submit();
                    };

                    document.addEventListener('change', (event) => {
                        const field = event.target.closest('#history-filter-form [data-auto-filter="instant"]');

                        if (field) {
                            submitFilter(field.form);
                        }
                    });

                    document.addEventListener('input', (event) => {
                        const field = event.target.closest('#history-filter-form [data-auto-filter="debounced"]');

                        if (!field) return;

                        clearTimeout(filterTimer);
                        filterTimer = setTimeout(() => submitFilter(field.form), 500);
                    });

                    document.addEventListener('click', (event) => {
                        const exportLink = event.target.closest('#history-export-link');

                        if (!exportLink) return;

                        const form = document.getElementById('history-filter-form');
                        if (!form) return;

                        const params = new URLSearchParams(new FormData(form));
                        exportLink.href = `${exportLink.dataset.baseUrl}?${params.toString()}`;
                    });

                    window.setInterval(refreshPage, Math.max(interval, 3000));
                })();
            </script>
        @endif
    </body>
</html>
