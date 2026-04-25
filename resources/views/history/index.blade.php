@php
    $statusStyles = [
        'borrowed' => 'bg-rose-500 text-white',
        'returned' => 'bg-blue-500 text-white',
        'late' => 'bg-amber-500 text-white',
    ];

    $statusLabels = [
        'borrowed' => 'Dipinjam',
        'returned' => 'Selesai',
        'late' => 'Terlambat',
    ];
@endphp

<x-siemola-layout title="Histori Peminjaman" active-menu="Histori Peminjaman" user-role="Staf">
    <section class="space-y-6">
        <form id="history-filter-form" method="GET" action="{{ route('history.index') }}" class="space-y-6">
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

            <section class="rounded-[24px] bg-white p-5 shadow-[0_18px_45px_rgba(15,23,42,0.05)] ring-1 ring-slate-200/70 sm:p-6">
                <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_240px] xl:items-end">
                    <div>
                        <h2 class="text-xl font-extrabold text-slate-950">Batas Waktu</h2>

                        <div class="mt-5 grid gap-5 lg:grid-cols-[minmax(0,1.4fr)_minmax(260px,1fr)]">
                            <div>
                                <label class="siemola-label">Tanggal</label>
                                <div class="grid gap-3 sm:grid-cols-2">
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

        <section class="overflow-hidden rounded-[24px] bg-white shadow-[0_18px_45px_rgba(15,23,42,0.05)] ring-1 ring-slate-200/70">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-slate-950">
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
                            <tr class="border-b border-slate-100 last:border-b-0">
                                <td class="siemola-td text-center">{{ $borrowing->borrowed_at?->format('d/m/Y') }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->borrowed_at?->format('H:i') }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->returned_at?->format('H:i') ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->student?->name ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->student?->study_program ?? '-' }}</td>
                                <td class="siemola-td text-center">{{ $borrowing->locker?->code ?? '-' }}</td>
                                <td class="siemola-td text-center">
                                    <span class="inline-flex min-w-28 justify-center rounded-full px-4 py-2 text-sm font-bold {{ $statusStyles[$borrowing->status] ?? 'bg-slate-200 text-slate-600' }}">
                                        {{ $statusLabels[$borrowing->status] ?? ucfirst($borrowing->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center text-sm font-medium text-slate-400">Belum ada histori peminjaman untuk filter ini.</td>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('history-filter-form');
            const exportLink = document.getElementById('history-export-link');
            let timer;

            const submitFilter = () => {
                if (form.requestSubmit) {
                    form.requestSubmit();
                    return;
                }

                form.submit();
            };

            form.querySelectorAll('[data-auto-filter="instant"]').forEach((input) => {
                input.addEventListener('change', submitFilter);
            });

            form.querySelectorAll('[data-auto-filter="debounced"]').forEach((input) => {
                input.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(submitFilter, 500);
                });
            });

            exportLink.addEventListener('click', () => {
                const params = new URLSearchParams(new FormData(form));
                exportLink.href = `${exportLink.dataset.baseUrl}?${params.toString()}`;
            });
        });
    </script>
</x-siemola-layout>
