<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} | Login</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="siemola-login-page">
        <div class="siemola-login-shell">
            <section class="siemola-login-panel" aria-label="SIEMOLA overview">
                <div class="siemola-login-brand">
                    <div class="siemola-login-logo">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-8 w-8">
                            <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z" />
                            <path d="M12 3v18M4 7l8 4 8-4M8 5l8 4" />
                        </svg>
                    </div>
                    <div>
                        <p class="siemola-login-brand-title">SIEMOLA</p>
                        <p class="siemola-login-brand-subtitle">Smart Locker System</p>
                    </div>
                </div>

                <div class="siemola-login-copy">
                    <p class="siemola-login-eyebrow">Staff & Admin Access</p>
                    <h1 class="siemola-login-heading">Kelola locker, RFID, dan histori peminjaman dalam satu dashboard.</h1>
                    <p class="siemola-login-description">
                        Masuk untuk memantau status locker, data mahasiswa, kartu RFID, dan notifikasi peminjaman yang terlambat.
                    </p>
                </div>

                <div class="siemola-login-metrics">
                    <div class="siemola-login-metric">
                        <p class="siemola-login-metric-value">RFID</p>
                        <p class="siemola-login-metric-label">Tap Access</p>
                    </div>
                    <div class="siemola-login-metric">
                        <p class="siemola-login-metric-value">12</p>
                        <p class="siemola-login-metric-label">Locker Slots</p>
                    </div>
                    <div class="siemola-login-metric">
                        <p class="siemola-login-metric-value">17:00</p>
                        <p class="siemola-login-metric-label">Late Check</p>
                    </div>
                </div>
            </section>

            <main class="siemola-login-form-side">
                <section class="siemola-login-card">
                    <div class="siemola-login-mobile-brand">
                        <div class="siemola-login-mobile-logo">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-7 w-7">
                                <path d="M12 3 4 7v10l8 4 8-4V7l-8-4Z" />
                                <path d="M12 3v18M4 7l8 4 8-4M8 5l8 4" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-xl font-extrabold text-slate-950">SIEMOLA</p>
                            <p class="text-sm font-medium text-slate-500">Smart Locker System</p>
                        </div>
                    </div>

                    <div>
                        <h2 class="siemola-login-title">Masuk Dashboard</h2>
                        <p class="siemola-login-caption">Gunakan akun staff atau admin yang sudah terdaftar.</p>
                    </div>

                    @if (session('status'))
                        <div class="siemola-login-status">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="siemola-login-form">
                        @csrf

                        <div class="siemola-login-field">
                            <label for="email" class="siemola-login-label">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="siemola-login-input" required autofocus autocomplete="username" placeholder="nama@email.com">
                            @error('email')
                                <p class="siemola-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="siemola-login-field">
                            <label for="password" class="siemola-login-label">Password</label>
                            <input id="password" name="password" type="password" class="siemola-login-input" required autocomplete="current-password" placeholder="Masukkan password">
                            @error('password')
                                <p class="siemola-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="siemola-login-options">
                            <label for="remember_me" class="siemola-login-check">
                                <input id="remember_me" type="checkbox" class="siemola-login-checkbox" name="remember">
                                <span>Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="siemola-login-link">Lupa password?</a>
                            @endif
                        </div>

                        <button type="submit" class="siemola-login-button">Login</button>
                    </form>
                </section>
            </main>
        </div>
    </body>
</html>
