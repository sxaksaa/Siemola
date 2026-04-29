<x-siemola-layout title="Profile" active-menu="Profile" user-role="{{ ucfirst($user->role) }}" sidebar-note="Kelola identitas akun dan keamanan login dashboard SIEMOLA.">
    <section class="siemola-profile-hero">
        <div class="siemola-profile-hero-inner">
            <div class="siemola-profile-identity">
                <div class="siemola-profile-avatar">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="siemola-profile-name">{{ $user->name }}</h2>
                    <p class="siemola-profile-meta">{{ $user->email }} · {{ ucfirst($user->role) }}</p>
                </div>
            </div>

            <span class="siemola-profile-badge">{{ ucfirst($user->status ?? 'active') }}</span>
        </div>
    </section>

    <section class="siemola-profile-grid">
        <div class="siemola-profile-stack-clean">
            <article class="siemola-profile-panel">
                <header>
                    <h2 class="siemola-profile-title">Informasi Akun</h2>
                    <p class="siemola-profile-description">Perbarui nama dan email yang dipakai untuk masuk ke dashboard.</p>
                </header>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                    @csrf
                </form>

                <form method="POST" action="{{ route('profile.update') }}" class="siemola-profile-form">
                    @csrf
                    @method('patch')

                    <div>
                        <label for="name" class="siemola-label">Nama</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" class="siemola-input" required autofocus autocomplete="name">
                        @error('name')
                            <p class="siemola-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="siemola-label">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" class="siemola-input" required autocomplete="username">
                        @error('email')
                            <p class="siemola-error">{{ $message }}</p>
                        @enderror

                        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                            <p class="siemola-profile-danger-text">
                                Email belum diverifikasi.
                                <button form="send-verification" class="siemola-login-link">Kirim ulang verifikasi.</button>
                            </p>

                            @if (session('status') === 'verification-link-sent')
                                <p class="siemola-profile-saved">Link verifikasi baru sudah dikirim.</p>
                            @endif
                        @endif
                    </div>

                    <div class="siemola-profile-actions">
                        <button type="submit" class="siemola-primary-button">Simpan Profile</button>

                        @if (session('status') === 'profile-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="siemola-profile-saved">
                                Profile tersimpan.
                            </p>
                        @endif
                    </div>
                </form>
            </article>

            <article id="password-section" class="siemola-profile-panel">
                <header>
                    <h2 class="siemola-profile-title">Keamanan Login</h2>
                    <p class="siemola-profile-description">Ganti password secara berkala agar akses dashboard tetap aman.</p>
                </header>

                <form method="POST" action="{{ route('password.update') }}" class="siemola-profile-form">
                    @csrf
                    @method('put')

                    <div>
                        <label for="update_password_current_password" class="siemola-label">Password Saat Ini</label>
                        <input id="update_password_current_password" name="current_password" type="password" class="siemola-input" autocomplete="current-password">
                        @if ($errors->updatePassword->has('current_password'))
                            <p class="siemola-error">{{ $errors->updatePassword->first('current_password') }}</p>
                        @endif
                    </div>

                    <div class="siemola-form-grid">
                        <div>
                            <label for="update_password_password" class="siemola-label">Password Baru</label>
                            <input id="update_password_password" name="password" type="password" class="siemola-input" autocomplete="new-password">
                            @if ($errors->updatePassword->has('password'))
                                <p class="siemola-error">{{ $errors->updatePassword->first('password') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="update_password_password_confirmation" class="siemola-label">Konfirmasi Password</label>
                            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="siemola-input" autocomplete="new-password">
                            @if ($errors->updatePassword->has('password_confirmation'))
                                <p class="siemola-error">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="siemola-profile-actions">
                        <button type="submit" class="siemola-primary-button">Update Password</button>

                        @if (session('status') === 'password-updated')
                            <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="siemola-profile-saved">
                                Password diperbarui.
                            </p>
                        @endif
                    </div>
                </form>
            </article>
        </div>

        <aside class="siemola-profile-panel-danger">
            <h2 class="siemola-profile-title">Hapus Akun</h2>
            <p class="siemola-profile-danger-text">
                Akun yang dihapus tidak bisa dipakai lagi untuk masuk ke dashboard. Pastikan akun admin atau staff pengganti sudah tersedia sebelum melanjutkan.
            </p>

            <button type="button" class="siemola-profile-danger-button" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
                Hapus Akun Saya
            </button>

            <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                <form method="POST" action="{{ route('profile.destroy') }}" class="p-6">
                    @csrf
                    @method('delete')

                    <h2 class="siemola-profile-title">Konfirmasi hapus akun</h2>
                    <p class="siemola-profile-danger-text">Masukkan password untuk menghapus akun ini secara permanen.</p>

                    <div class="mt-6">
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" class="siemola-input" placeholder="Password">
                        @if ($errors->userDeletion->has('password'))
                            <p class="siemola-error">{{ $errors->userDeletion->first('password') }}</p>
                        @endif
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" class="siemola-secondary-button" x-on:click="$dispatch('close')">Batal</button>
                        <button type="submit" class="siemola-profile-danger-button mt-0">Hapus Akun</button>
                    </div>
                </form>
            </x-modal>
        </aside>
    </section>
</x-siemola-layout>
