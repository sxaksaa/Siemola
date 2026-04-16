<x-siemola-layout title="Data Mahasiswa" active-menu="Data Mahasiswa" user-role="Staff" sidebar-note="Data mahasiswa dipakai buat identitas peminjam. UID RFID akan dicocokkan ke mahasiswa ini saat kartu ditap di perangkat locker.">
    <section class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <form method="GET" action="{{ route('students.index') }}" class="w-full xl:max-w-4xl">
                <label class="siemola-search-shell xl:max-w-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5 text-slate-400">
                        <path d="m21 21-4.35-4.35M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari ..." class="siemola-search-input">
                </label>
            </form>

            <a href="{{ route('students.create') }}" class="siemola-primary-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-5 w-5">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Tambah Data</span>
            </a>
        </div>

        <section class="overflow-hidden rounded-[30px] bg-white shadow-[0_24px_70px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-white text-slate-900">
                            <th class="siemola-th">Nama</th>
                            <th class="siemola-th">NIM</th>
                            <th class="siemola-th">UID RFID</th>
                            <th class="siemola-th">Program Studi</th>
                            <th class="siemola-th">Kelas</th>
                            <th class="siemola-th text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="siemola-td font-medium text-slate-800">{{ $student->name }}</td>
                                <td class="siemola-td">{{ $student->nim }}</td>
                                <td class="siemola-td">{{ $student->rfid_uid }}</td>
                                <td class="siemola-td">{{ $student->study_program }}</td>
                                <td class="siemola-td">{{ $student->class_name }}</td>
                                <td class="siemola-td">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('students.edit', $student) }}" class="siemola-icon-action text-blue-500" aria-label="Edit mahasiswa">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                                <path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="m13.5 6.5 3 3" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Hapus data mahasiswa ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="siemola-icon-action text-rose-500" aria-label="Hapus mahasiswa">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                                    <path d="M3 6h18M8 6V4h8v2m-7 4v7m4-7v7M6 6l1 14h10l1-14" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm font-medium text-slate-400">Belum ada data mahasiswa yang cocok dengan pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div>
            {{ $students->links() }}
        </div>

        <section class="grid gap-4 lg:grid-cols-3">
            <article class="siemola-info-card">
                <h2 class="siemola-info-title">Isi tabel `students`</h2>
                <p class="siemola-info-text">Minimal ada `name`, `nim`, `rfid_uid`, `study_program`, dan `class_name`. Ini sudah cukup untuk identitas peminjam waktu RFID ditap.</p>
            </article>

            <article class="siemola-info-card">
                <h2 class="siemola-info-title">Alur RFID</h2>
                <p class="siemola-info-text">Perangkat kirim UID ke server, server cari UID di tabel mahasiswa, lalu cek apakah mahasiswa itu boleh membuka locker yang diminta.</p>
            </article>

            <article class="siemola-info-card">
                <h2 class="siemola-info-title">Riwayat transaksi</h2>
                <p class="siemola-info-text">Setiap buka locker sebaiknya dicatat ke tabel `borrowings`, jadi kamu bisa tahu siapa pinjam, kapan pinjam, kapan kembali, dan apakah telat.</p>
            </article>
        </section>
    </section>
</x-siemola-layout>
