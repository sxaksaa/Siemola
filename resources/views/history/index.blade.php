@php
    $statusStyles = [
        'borrowed' => 'siemola-status-borrowed',
        'returned' => 'siemola-status-returned',
        'late' => 'siemola-status-late',
    ];

    $statusLabels = [
        'borrowed' => 'Dipinjam',
        'returned' => 'Selesai',
        'late' => 'Terlambat',
    ];
@endphp

<x-siemola-layout title="Histori Peminjaman" active-menu="Histori Peminjaman" user-role="Staf" :auto-refresh="true">
    <section class="siemola-page-stack">
        <form id="history-filter-form" method="GET" action="{{ route('history.index') }}" class="siemola-page-stack">
            <label class="siemola-search-shell xl:max-w-none">
                <input
                    type="text"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Cari ..."
                    class="siemola-search-input"
                    data-auto-filter="debounced"
                >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5 text-slate-400">
                    <path d="m21 21-4.35-4.35M10.8 18a7.2 7.2 0 1 1 0-14.4 7.2 7.2 0 0 1 0 14.4Z" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </label>

            <section class="siemola-panel">
                <div class="siemola-filter-grid">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-950">Batas Waktu</h2>

                        <div class="siemola-filter-fields">
                            <div>
                                <label class="siemola-label">Tanggal</label>
                                <div class="siemola-date-grid">
                                    <input type="date" name="start_date" value="{{ $filters['start_date'] }}" class="siemola-input" data-auto-filter="instant">
                                    <input type="date" name="end_date" value="{{ $filters['end_date'] }}" class="siemola-input" data-auto-filter="instant">
                                </div>
                            </div>

                            <div>
                                <label for="study_program" class="siemola-label">Program Studi</label>
                                <select id="study_program" name="study_program" class="siemola-input" data-auto-filter="instant">
                                    <option value="">Semua Program Studi</option>
                                    @foreach ($studyPrograms as $studyProgram)
                                        <option value="{{ $studyProgram }}" @selected($filters['study_program'] === $studyProgram)>{{ $studyProgram }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <a id="history-export-link" href="{{ route('history.export', request()->query()) }}" data-base-url="{{ route('history.export') }}" target="_blank" class="siemola-primary-button w-full">
                        Export PDF
                    </a>
                </div>
            </section>
        </form>

        <section class="siemola-table-card-compact">
            <div class="siemola-table-scroll">
                <table class="siemola-table">
                    <thead>
                        <tr class="siemola-table-head-muted">
                            <th class="siemola-th text-center">Tanggal</th>
                            <th class="siemola-th text-center">Jam Pinjam</th>
                            <th class="siemola-th text-center">Jam Kembali</th>
                            <th class="siemola-th text-center">Mahasiswa</th>
                            <th class="siemola-th text-center">Program Studi</th>
                            <th class="siemola-th text-center">Nomor Loker</th>
                            <th class="siemola-th text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($borrowings as $borrowing)
                            @php
                                $borrowedAt = $borrowing->borrowed_at?->copy()->timezone($displayTimezone);
                                $returnedAt = $borrowing->returned_at?->copy()->timezone($displayTimezone);
                            @endphp
                            <tr class="siemola-table-row">
                                <td class="siemola-td text-center">{{ $borrowedAt?->format('d/m/Y') }}</td>
                                <td class="siemola-td text-center">{{ $borrowedAt?->format('H:i') }}</td>
                                <td class="siemola-td text-center">{{ $returnedAt?->format('H:i') ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->student?->name ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->student?->study_program ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->locker?->code ?? '-' }}</td>
                                <td class="siemola-td text-center">
                                    <span class="siemola-status-pill {{ $statusStyles[$borrowing->status] ?? 'siemola-status-neutral' }}">
                                        {{ $statusLabels[$borrowing->status] ?? ucfirst($borrowing->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="siemola-table-empty">Belum ada histori peminjaman untuk filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div>
            {{ $borrowings->links() }}
        </div>
    </section>

</x-siemola-layout>
