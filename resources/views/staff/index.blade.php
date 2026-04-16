<x-siemola-layout title="Data Staf" active-menu="Data Staf" user-role="Admin" sidebar-note="Admin mengelola akun staff yang bertugas memonitor dashboard dan mengatur data mahasiswa.">
    <section class="space-y-6">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <form method="GET" action="{{ route('staff.index') }}" class="w-full xl:max-w-4xl">
                <label class="siemola-search-shell xl:max-w-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5 text-slate-400">
                        <path d="m21 21-4.35-4.35M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari ..." class="siemola-search-input">
                </label>
            </form>

            <a href="{{ route('staff.create') }}" class="siemola-primary-button">
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
                            <th class="siemola-th">NIK/NIP</th>
                            <th class="siemola-th">Email</th>
                            <th class="siemola-th">No Hp</th>
                            <th class="siemola-th">Status</th>
                            <th class="siemola-th text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($staffs as $staff)
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="siemola-td font-medium text-slate-800">{{ $staff->name }}</td>
                                <td class="siemola-td">{{ $staff->nik_nip }}</td>
                                <td class="siemola-td">{{ $staff->email }}</td>
                                <td class="siemola-td">{{ $staff->phone ?: '-' }}</td>
                                <td class="siemola-td">
                                    <span class="siemola-badge {{ $staff->status === 'active' ? 'siemola-badge-active' : 'siemola-badge-inactive' }}">
                                        {{ ucfirst($staff->status) }}
                                    </span>
                                </td>
                                <td class="siemola-td">
                                    <div class="flex items-center justify-center gap-3">
                                        <a href="{{ route('staff.edit', $staff) }}" class="siemola-icon-action text-blue-500" aria-label="Edit staff">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                                <path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="m13.5 6.5 3 3" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('staff.destroy', $staff) }}" onsubmit="return confirm('Hapus data staff ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="siemola-icon-action text-rose-500" aria-label="Hapus staff">
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
                                <td colspan="6" class="px-5 py-10 text-center text-sm font-medium text-slate-400">Belum ada data staff yang cocok dengan pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div>
            {{ $staffs->links() }}
        </div>
    </section>
</x-siemola-layout>
