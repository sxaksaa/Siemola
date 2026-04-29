<x-siemola-layout title="Dashboard" active-menu="Dashboard" user-role="{{ auth()->check() ? ucfirst(auth()->user()->role) : 'Mahasiswa' }}" sidebar-note="Pantau status loker, aktivitas RFID, dan transaksi peminjaman secara otomatis." :auto-refresh="true">
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
                <div class="siemola-chart-heading">
                    <div>
                        <h2 class="siemola-card-title">Aktivitas Loker</h2>
                        <p class="siemola-card-caption">Pergerakan pinjam dan kembali selama 7 hari terakhir dari tap RFID.</p>
                    </div>

                    <div class="siemola-chart-legend" aria-label="Legenda grafik aktivitas loker">
                        <span class="siemola-chart-pill siemola-chart-pill-blue">
                            <span class="siemola-chart-dot"></span>
                            Pinjam
                        </span>
                        <span class="siemola-chart-pill siemola-chart-pill-green">
                            <span class="siemola-chart-dot"></span>
                            Kembali
                        </span>
                    </div>
                </div>

                <div class="siemola-chart-area">
                    @php
                        $hasActivity = $activityChart->sum(fn ($item) => $item['borrowed'] + $item['returned']) > 0;
                    @endphp

                    @if ($hasActivity)
                        @php
                            $chartItems = $activityChart->values();
                            $chartWidth = 760;
                            $chartHeight = 300;
                            $chartPadding = 44;
                            $usableWidth = $chartWidth - ($chartPadding * 2);
                            $usableHeight = $chartHeight - ($chartPadding * 2);
                            $itemCount = $chartItems->count();
                            $axisMax = max($maxActivityValue, 1);
                            $baselineY = $chartHeight - $chartPadding;
                            $makePoints = function (string $key) use ($chartItems, $itemCount, $chartWidth, $chartPadding, $usableWidth, $usableHeight, $axisMax) {
                                return $chartItems->map(function ($item, $index) use ($key, $itemCount, $chartWidth, $chartPadding, $usableWidth, $usableHeight, $axisMax) {
                                    $x = $itemCount > 1
                                        ? $chartPadding + (($usableWidth / ($itemCount - 1)) * $index)
                                        : $chartWidth / 2;
                                    $value = (int) $item[$key];
                                    $y = $chartPadding + (($axisMax - $value) / $axisMax * $usableHeight);

                                    return [
                                        'x' => round($x, 2),
                                        'y' => round($y, 2),
                                        'label' => $item['label'],
                                        'value' => $value,
                                        'borrowed' => (int) $item['borrowed'],
                                        'returned' => (int) $item['returned'],
                                    ];
                                });
                            };
                            $borrowedPoints = $makePoints('borrowed');
                            $returnedPoints = $makePoints('returned');
                            $borrowedPointString = $borrowedPoints->map(fn ($point) => "{$point['x']},{$point['y']}")->implode(' ');
                            $returnedPointString = $returnedPoints->map(fn ($point) => "{$point['x']},{$point['y']}")->implode(' ');
                            $borrowedAreaPointString = "{$chartPadding},{$baselineY} {$borrowedPointString} ".($chartWidth - $chartPadding).",{$baselineY}";
                        @endphp

                        <div class="siemola-chart-frame">
                            <div class="siemola-chart-scroll">
                                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight + 54 }}" role="img" aria-label="Grafik aktivitas pinjam dan kembali 7 hari terakhir" class="siemola-chart-svg">
                                    <defs>
                                        <linearGradient id="activity-line-fill" x1="0" x2="0" y1="0" y2="1">
                                            <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.22" />
                                            <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                        </linearGradient>
                                    </defs>

                                    @foreach ([0, 1, 2, 3] as $lineIndex)
                                        @php
                                            $gridY = $chartPadding + (($usableHeight / 3) * $lineIndex);
                                            $gridValue = round($axisMax - (($axisMax / 3) * $lineIndex));
                                        @endphp
                                        <line x1="{{ $chartPadding }}" y1="{{ $gridY }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $gridY }}" stroke="#e2e8f0" stroke-width="1" />
                                        <text x="12" y="{{ $gridY + 5 }}" fill="#94a3b8" font-size="12" font-weight="700">{{ $gridValue }}</text>
                                    @endforeach

                                    <polyline points="{{ $borrowedAreaPointString }}" fill="url(#activity-line-fill)" stroke="none" />
                                    <polyline points="{{ $borrowedPointString }}" fill="none" stroke="#2563eb" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
                                    <polyline points="{{ $returnedPointString }}" fill="none" stroke="#059669" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />

                                    @foreach ($borrowedPoints as $point)
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="8" fill="#ffffff" stroke="#2563eb" stroke-width="4" />
                                        <title>{{ $point['label'] }}: {{ $point['borrowed'] }} pinjam, {{ $point['returned'] }} kembali</title>
                                        @if ($point['value'] > 0)
                                            <text x="{{ $point['x'] }}" y="{{ $point['y'] - 16 }}" text-anchor="middle" fill="#1d4ed8" font-size="13" font-weight="800">{{ $point['value'] }}</text>
                                        @endif
                                    @endforeach

                                    @foreach ($returnedPoints as $point)
                                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="7" fill="#ffffff" stroke="#059669" stroke-width="4" />
                                        <title>{{ $point['label'] }}: {{ $point['borrowed'] }} pinjam, {{ $point['returned'] }} kembali</title>
                                        @if ($point['value'] > 0)
                                            <text x="{{ $point['x'] }}" y="{{ $point['y'] + 24 }}" text-anchor="middle" fill="#047857" font-size="13" font-weight="800">{{ $point['value'] }}</text>
                                        @endif
                                    @endforeach

                                    @foreach ($chartItems as $index => $item)
                                        @php
                                            $labelX = $itemCount > 1
                                                ? $chartPadding + (($usableWidth / ($itemCount - 1)) * $index)
                                                : $chartWidth / 2;
                                        @endphp
                                        <text x="{{ round($labelX, 2) }}" y="{{ $chartHeight + 12 }}" text-anchor="middle" fill="#475569" font-size="12" font-weight="700">
                                            {{ $item['label'] }}
                                        </text>
                                    @endforeach
                                </svg>
                            </div>
                        </div>
                    @else
                        <div class="siemola-chart-empty">
                            <p class="siemola-chart-empty-text">Belum ada aktivitas pinjam atau kembali dalam 7 hari terakhir.</p>
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

                    <div class="siemola-sync-card siemola-sync-{{ $espSyncSummary['state'] }}" data-siemola-sync-card>
                        <div class="siemola-sync-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" class="h-5 w-5">
                                <path d="M4 12a8 8 0 0 1 13.66-5.66M20 12a8 8 0 0 1-13.66 5.66M8 6H4V2m12 16h4v4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="siemola-sync-title">Terakhir sinkron ESP</p>
                                <span class="siemola-sync-status" data-siemola-sync-status>{{ $espSyncSummary['status'] }}</span>
                            </div>
                            <p class="siemola-sync-time">{{ $espSyncSummary['time'] }}</p>
                            <p class="siemola-sync-meta">
                                {{ $espSyncSummary['locker'] }} -
                                @if ($espSyncSummary['synced_at'])
                                    <span data-siemola-relative-time="{{ $espSyncSummary['synced_at'] }}">{{ $espSyncSummary['relative'] }}</span>
                                @else
                                    <span>{{ $espSyncSummary['relative'] }}</span>
                                @endif
                            </p>
                            <p class="siemola-sync-meta">L2-L12 memakai data dummy, jadi tidak ikut sinkron ESP.</p>
                        </div>
                    </div>
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
                <article class="siemola-locker-card {{ $lockerClasses[$locker['state']] ?? 'siemola-locker-available' }} {{ $locker['is_real_esp'] ? 'siemola-locker-real' : '' }} {{ $locker['is_dummy'] ? 'siemola-locker-dummy' : '' }}">
                    @if ($locker['is_real_esp'])
                        <span class="siemola-locker-esp-badge">ESP Aktif</span>
                    @elseif ($locker['is_dummy'])
                        <span class="siemola-locker-dummy-badge">Dummy</span>
                    @endif
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
