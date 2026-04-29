<x-siemola-layout title="Dashboard" active-menu="Dashboard" user-role="{{ auth()->check() ? ucfirst(auth()->user()->role) : 'Mahasiswa' }}" sidebar-note="Dashboard ini nanti bisa menampilkan data real dari tabel mahasiswa, locker, alat, dan transaksi peminjaman." :auto-refresh="true">
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
        <section class="siemola-stat-grid">
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

        <section class="siemola-dashboard-grid">
            <article class="siemola-dashboard-card">
                <div>
                    <h2 class="siemola-card-title">Statistik Sistem</h2>
                    <p class="siemola-card-caption">Grafik jumlah mahasiswa berdasarkan program studi yang tersimpan di database saat ini.</p>
                </div>

                <div class="siemola-chart-area">
                    @if ($studyProgramChart->isNotEmpty())
                        @php
                            $chartItems = $studyProgramChart->values();
                            $chartWidth = 720;
                            $chartHeight = 300;
                            $chartPadding = 44;
                            $usableWidth = $chartWidth - ($chartPadding * 2);
                            $usableHeight = $chartHeight - ($chartPadding * 2);
                            $itemCount = $chartItems->count();
                            $points = $chartItems->map(function ($item, $index) use ($itemCount, $chartWidth, $chartPadding, $usableWidth, $usableHeight, $maxStudyProgramValue) {
                                $x = $itemCount > 1
                                    ? $chartPadding + (($usableWidth / ($itemCount - 1)) * $index)
                                    : $chartWidth / 2;
                                $y = $chartPadding + (($maxStudyProgramValue - $item['value']) / $maxStudyProgramValue * $usableHeight);

                                return [
                                    'x' => round($x, 2),
                                    'y' => round($y, 2),
                                    'label' => $item['label'],
                                    'value' => $item['value'],
                                ];
                            });
                            $pointString = $points->map(fn ($point) => "{$point['x']},{$point['y']}")->implode(' ');
                            $areaPointString = "{$chartPadding},".($chartHeight - $chartPadding).' '.$pointString.' '.($chartWidth - $chartPadding).','.($chartHeight - $chartPadding);
                        @endphp

                        <div class="siemola-chart-frame">
                            <div class="siemola-chart-scroll">
                                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 54 }}" role="img" aria-label="Grafik line statistik mahasiswa per program studi" class="siemola-chart-svg">
                                    <defs>
                                        <linearGradient id="study-line-fill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.22" />
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>

                                    @foreach ([0, 1, 2, 3] as $lineIndex)
                                        @php
                                            $gridY = $chartPadding + (($usableHeight / 3) * $lineIndex);
                                            $gridValue = round($maxStudyProgramValue - (($maxStudyProgramValue / 3) * $lineIndex));
                                        @endphp
                                        <line x1="{{ $chartPadding }}" y1="{{ $gridY }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $gridY }}" stroke="#e2e8f0" stroke-width="1" />
                                        <text x="12" y="{{ $gridY + 5 }}" fill="#94a3b8" font-size="12" font-weight="700">{{ $gridValue }}</text>
                                    @endforeach

                                    <polyline points="{{ $areaPointString }}" fill="url(#study-line-fill)" stroke="none" />
                                    <polyline points="{{ $pointString }}" fill="none" stroke="#2563eb" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />

                                    @foreach ($points as $point)
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="8" fill="#ffffff" stroke="#2563eb" stroke-width="4" />
                                        <text x="{{ $point['x'] }}" y="{{ $point['y'] - 16 }}" text-anchor="middle" fill="#0f172a" font-size="14" font-weight="800">{{ $point['value'] }}</text>
                                        <text x="{{ $point['x'] }}" y="{{ $chartHeight + 12 }}" text-anchor="middle" fill="#475569" font-size="12" font-weight="700">
                                            {{ \Illuminate\Support\Str::limit($point['label'], 16) }}
                                        </text>
                                    @endforeach
                                </svg>
                            </div>
                        </div>
                    @else
                        <div class="siemola-chart-empty">
                            <p class="siemola-chart-empty-text">Belum ada data mahasiswa untuk divisualisasikan.</p>
                        </div>
                    @endif
                </div>
            </article>

            <article class="siemola-dashboard-card">
                <div>
                    <h2 class="siemola-card-title">Ringkasan Loker</h2>
                    <p class="siemola-card-caption">Status operasional locker berdasarkan data real dari tabel `lockers`.</p>
                </div>

                <div class="siemola-summary-list">
                    @foreach ($lockerStatusSummary as $summary)
                        <div class="siemola-summary-row {{ $summary['tone'] }}">
                            <span class="text-sm font-bold">{{ $summary['label'] }}</span>
                            <span class="text-2xl font-extrabold">{{ $summary['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    @endunless

    <section class="siemola-locker-section {{ $isStudentView ? '' : 'siemola-locker-section-spaced' }}">
        <div>
            <h2 class="siemola-card-title">Status Loker</h2>
            <p class="siemola-card-caption">{{ $isStudentView ? 'Mahasiswa hanya bisa melihat ketersediaan locker sebelum melakukan tap RFID.' : 'Informasi Status Ketersedian Locker secara Real-time' }}</p>
        </div>

        <div class="siemola-locker-grid">
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
