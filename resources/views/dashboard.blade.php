<x-siemola-layout title="Dashboard" active-menu="Dashboard" user-role="{{ auth()->check() ? ucfirst(auth()->user()->role) : 'Mahasiswa' }}" sidebar-note="Dashboard ini nanti bisa menampilkan data real dari tabel mahasiswa, locker, alat, dan transaksi peminjaman.">
    @php
        $accentClasses = [
            'violet' => 'siemola-accent-violet',
            'blue' => 'siemola-accent-blue',
            'green' => 'siemola-accent-green',
            'red' => 'siemola-accent-red',
            'amber' => 'siemola-accent-amber',
        ];

        $lockerClasses = [
            'available' => 'siemola-locker-available',
            'borrowed' => 'siemola-locker-borrowed',
            'late' => 'siemola-locker-late',
        ];
    @endphp

    @unless ($isStudentView)
        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($stats as $stat)
                <article class="siemola-stat-card {{ $accentClasses[$stat['accent']] ?? 'siemola-accent-blue' }}">
                    <div>
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <p class="mt-3 text-4xl font-extrabold tracking-tight text-[var(--siemola-accent-strong)]">{{ $stat['value'] }}</p>
                    </div>

                    <div class="siemola-stat-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-6 w-6">
                            <path d="{{ $stat['icon_path'] }}" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="mt-8 grid gap-6 xl:grid-cols-[minmax(0,2fr)_minmax(320px,1fr)]">
            <article class="rounded-[30px] bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 sm:p-7">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Statistik Sistem</h2>
                    <p class="mt-1 text-sm font-medium text-slate-400">Distribusi mahasiswa aktif berdasarkan program studi yang tersimpan di database saat ini.</p>
                </div>

                <div class="mt-8 space-y-5">
                    @forelse ($studyProgramChart as $item)
                        <div class="grid gap-3 md:grid-cols-[220px_minmax(0,1fr)_60px] md:items-center">
                            <div class="text-sm font-semibold text-slate-600">{{ $item['label'] }}</div>
                            <div class="h-4 overflow-hidden rounded-full bg-slate-100">
                                <div
                                    class="h-full rounded-full bg-gradient-to-r {{ $item['bar'] }}"
                                    style="width: {{ max(($item['value'] / $maxStudyProgramValue) * 100, 8) }}%;"
                                ></div>
                            </div>
                            <div class="text-right text-sm font-extrabold text-slate-900">{{ $item['value'] }}</div>
                        </div>
                    @empty
                        <p class="text-sm font-medium text-slate-400">Belum ada data mahasiswa untuk divisualisasikan.</p>
                    @endforelse
                </div>
            </article>

            <article class="rounded-[30px] bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 sm:p-7">
                <div>
                    <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Ringkasan Loker</h2>
                    <p class="mt-1 text-sm font-medium text-slate-400">Status operasional locker berdasarkan data real dari tabel `lockers`.</p>
                </div>

                <div class="mt-8 space-y-4">
                    @foreach ($lockerStatusSummary as $summary)
                        <div class="flex items-center justify-between rounded-2xl px-4 py-4 ring-1 {{ $summary['tone'] }}">
                            <span class="text-sm font-bold">{{ $summary['label'] }}</span>
                            <span class="text-2xl font-extrabold">{{ $summary['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    @endunless

    <section class="{{ $isStudentView ? '' : 'mt-8' }} rounded-[30px] bg-white p-6 shadow-[0_24px_70px_rgba(15,23,42,0.08)] ring-1 ring-slate-200/70 sm:p-7">
        <div>
            <h2 class="text-2xl font-extrabold tracking-tight text-slate-950">Status Loker</h2>
            <p class="mt-1 text-sm font-medium text-slate-400">{{ $isStudentView ? 'Mahasiswa hanya bisa melihat ketersediaan locker sebelum melakukan tap RFID.' : 'Informasi Status Ketersedian Locker secara Real-time' }}</p>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            @foreach ($lockers as $locker)
                <article class="siemola-locker-card {{ $lockerClasses[$locker['state']] ?? 'siemola-locker-available' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-7 w-7">
                        <path d="{{ $locker['icon_path'] }}" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <p class="mt-3 text-lg font-extrabold tracking-tight">{{ $locker['name'] }}</p>
                    <p class="mt-1 text-center text-sm font-semibold">{{ $locker['status'] }}</p>
                </article>
            @endforeach
        </div>
    </section>
</x-siemola-layout>
