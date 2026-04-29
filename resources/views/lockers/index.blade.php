<x-siemola-layout title="Data Loker" active-menu="Data Loker" user-role="Admin" sidebar-note="Admin mengelola identitas loker dan memantau status dari switch ESP." :auto-refresh="true">
    <section class="siemola-page-stack">
        <div class="siemola-toolbar">
            <form method="GET" action="{{ route('lockers.index') }}" class="siemola-toolbar-search">
                <label class="siemola-search-shell xl:max-w-none">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5 text-slate-400">
                        <path d="m21 21-4.35-4.35M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari ..." class="siemola-search-input">
                </label>
            </form>

            <a href="{{ route('lockers.create') }}" class="siemola-primary-button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-5 w-5">
                    <path d="M12 5v14M5 12h14" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span>Tambah Data</span>
            </a>
        </div>

        <section class="siemola-table-card">
            <div class="siemola-table-scroll">
                <table class="siemola-table">
                    <thead>
                        <tr class="siemola-table-head">
                            <th class="siemola-th">Kode Loker</th>
                            <th class="siemola-th">Nama</th>
                            <th class="siemola-th">Device ID</th>
                            <th class="siemola-th">Sensor Switch</th>
                            <th class="siemola-th">Status</th>
                            <th class="siemola-th">Peminjam Aktif</th>
                            <th class="siemola-th">Catatan</th>
                            <th class="siemola-th text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lockers as $locker)
                            @php
                                $activeBorrowing = $locker->borrowings->first();
                                $needsAttention = match ($locker->switch_state) {
                                    0 => $activeBorrowing !== null,
                                    1 => $activeBorrowing === null,
                                    default => true,
                                };
                            @endphp
                            <tr class="siemola-table-row">
                                <td class="siemola-td font-medium text-slate-800">{{ $locker->code }}</td>
                                <td class="siemola-td">{{ $locker->name }}</td>
                                <td class="siemola-td">{{ $locker->device_id ?: '-' }}</td>
                                <td class="siemola-td">
                                    <span class="siemola-badge {{ match($locker->switch_state) {
                                        0 => 'siemola-badge-active',
                                        1 => 'siemola-badge-borrowed',
                                        default => 'siemola-badge-inactive',
                                    } }}">
                                        {{ match($locker->switch_state) {
                                            0 => 'Ada barang',
                                            1 => 'Kosong',
                                            default => 'Belum sinkron',
                                        } }}
                                    </span>
                                </td>
                                <td class="siemola-td">
                                    @if ($activeBorrowing)
                                        <div class="font-bold text-slate-800">{{ $activeBorrowing->student?->name ?? 'Mahasiswa' }}</div>
                                        <div class="mt-1 text-xs font-semibold text-slate-400">{{ $activeBorrowing->borrowed_rfid_uid }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="siemola-td">
                                    <span class="siemola-badge {{ $needsAttention ? 'siemola-badge-late' : 'siemola-badge-active' }}">
                                        {{ $needsAttention ? 'Perlu dicek' : 'Sinkron' }}
                                    </span>
                                </td>
                                <td class="siemola-td">
                                    <span class="siemola-badge {{ match($locker->status) {
                                        'available' => 'siemola-badge-active',
                                        'borrowed' => 'siemola-badge-borrowed',
                                        'late' => 'siemola-badge-late',
                                        default => 'siemola-badge-inactive',
                                    } }}">
                                        {{ match($locker->status) {
                                            'available' => 'Tersedia',
                                            'borrowed' => 'Sedang dipinjam',
                                            'late' => 'Telat Mengembalikan',
                                            default => ucfirst($locker->status),
                                        } }}
                                    </span>
                                </td>
                                <td class="siemola-td">
                                    <div class="siemola-row-actions">
                                        <a href="{{ route('lockers.edit', $locker) }}" class="siemola-icon-action text-blue-500" aria-label="Edit loker">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                                <path d="M4 20h4l10.5-10.5a2.12 2.12 0 0 0-3-3L5 17v3Z" stroke-linecap="round" stroke-linejoin="round" />
                                                <path d="m13.5 6.5 3 3" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('lockers.destroy', $locker) }}" onsubmit="return confirm('Hapus data loker ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="siemola-icon-action text-rose-500" aria-label="Hapus loker">
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
                                <td colspan="8" class="siemola-table-empty">Belum ada data loker yang cocok dengan pencarian.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div>
            {{ $lockers->links() }}
        </div>
    </section>
</x-siemola-layout>
