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
                'bell' => 'M15 17h5l-1.4-1.4a2 2 0 0 1-.6-1.42V11a6 6 0 1 0-12 0v3.18a2 2 0 0 1-.6 1.41L4 17h5m6 0a3 3 0 1 1-6 0m6 0H9',
                'user' => 'M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4Zm-7 9a7 7 0 0 1 14 0',
            ];

            $user = auth()->user();
            $menu = collect([
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard', 'roles' => ['guest', 'staff', 'admin']],
                ['label' => 'Data Mahasiswa', 'route' => 'students.index', 'icon' => 'students', 'roles' => ['staff']],
                ['label' => 'Data Staf', 'route' => 'staff.index', 'icon' => 'staff', 'roles' => ['admin']],
                ['label' => 'Data Loker', 'route' => 'lockers.index', 'icon' => 'locker', 'roles' => ['admin']],
            ])->filter(fn ($item) => in_array($user?->role ?? 'guest', $item['roles'], true));
        @endphp

        <div class="flex min-h-screen flex-col lg:flex-row">
            <aside class="siemola-sidebar w-full shrink-0 px-5 py-6 lg:min-h-screen lg:w-[260px]">
                <div class="flex items-center gap-3 rounded-[22px] bg-white/5 px-4 py-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-slate-900 shadow-lg shadow-slate-950/20">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-8 w-8">
                            <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z" />
                            <path d="M12 3v18M4 7l8 4 8-4M8 5l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold tracking-tight text-white">SIEMOLA</p>
                        <p class="text-sm text-slate-400">Smart Locker System</p>
                    </div>
                </div>

                <nav class="mt-10 space-y-3">
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

                <div class="mt-10 rounded-[24px] border border-white/10 bg-white/5 p-4 text-sm text-slate-300">
                    <p class="font-semibold text-white">Info cepat</p>
                    <p class="mt-2 leading-6 text-slate-400">{{ $sidebarNote ?? 'SIEMOLA dipakai untuk menghubungkan RFID, locker, dan riwayat peminjaman alat kampus.' }}</p>
                </div>
            </aside>

            <div class="flex-1 bg-[var(--siemola-surface)]">
                <header class="border-b border-slate-200 bg-white/90 px-5 py-5 backdrop-blur sm:px-7 lg:px-10">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-3xl font-extrabold tracking-tight text-slate-950">{{ $title ?? 'Dashboard' }}</h1>
                        </div>

                        <div class="flex items-center justify-between gap-4 sm:justify-end">
                            <button type="button" class="relative flex h-12 w-12 items-center justify-center rounded-full border border-slate-200 text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                    <path d="{{ $iconMap['bell'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="absolute right-2 top-2 inline-flex h-2.5 w-2.5 rounded-full bg-rose-500 ring-4 ring-white"></span>
                            </button>

                            @auth
                                <x-dropdown align="right" width="64" contentClasses="rounded-[22px] border border-slate-200 bg-white p-2 shadow-[0_24px_60px_rgba(15,23,42,0.12)]">
                                    <x-slot name="trigger">
                                        <button type="button" class="flex items-center gap-3 rounded-full px-2 py-1 transition hover:bg-slate-50">
                                            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-500 text-white shadow-lg shadow-blue-500/30">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-6 w-6">
                                                    <path d="{{ $iconMap['user'] }}" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </div>
                                            <div class="text-left">
                                                <p class="text-base font-bold text-slate-900">{{ $user->name }}</p>
                                                <p class="text-sm font-medium text-slate-400">{{ $userRole ?? 'Admin' }}</p>
                                            </div>
                                        </button>
                                    </x-slot>

                                    <x-slot name="content">
                                        <div class="space-y-1">
                                            <a href="{{ route('profile.edit') }}" class="siemola-dropdown-link">Profile</a>
                                            <a href="{{ route('profile.edit') }}#password-section" class="siemola-dropdown-link">Ubah Password</a>

                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="siemola-dropdown-link w-full text-left text-rose-500 hover:bg-rose-50">Logout</button>
                                            </form>
                                        </div>
                                    </x-slot>
                                </x-dropdown>
                            @endauth

                            @guest
                                <div class="flex items-center gap-3">
                                    <div class="hidden text-right sm:block">
                                        <p class="text-base font-bold text-slate-900">Mahasiswa</p>
                                        <p class="text-sm font-medium text-slate-400">Dashboard Publik</p>
                                    </div>

                                    <a href="{{ route('login') }}" class="siemola-primary-button px-6 py-3">
                                        Login Staff/Admin
                                    </a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </header>

                <main class="px-5 py-6 sm:px-7 lg:px-10">
                    @if (session('status'))
                        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
