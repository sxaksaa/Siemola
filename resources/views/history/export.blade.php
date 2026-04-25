@php
    $statusLabels = [
        'borrowed' => 'Dipinjam',
        'returned' => 'Selesai',
        'late' => 'Terlambat',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Export Histori Peminjaman</title>
        <style>
            body {
                color: #111827;
                font-family: Arial, sans-serif;
                margin: 32px;
            }

            h1 {
                font-size: 24px;
                margin: 0 0 8px;
            }

            .meta {
                color: #4b5563;
                font-size: 13px;
                margin-bottom: 24px;
            }

            table {
                border-collapse: collapse;
                font-size: 12px;
                width: 100%;
            }

            th,
            td {
                border: 1px solid #d1d5db;
                padding: 8px;
                text-align: left;
            }

            th {
                background: #f3f4f6;
                font-weight: 700;
            }

            .print-actions {
                margin-bottom: 20px;
            }

            .print-actions button {
                background: #2563eb;
                border: 0;
                border-radius: 10px;
                color: #ffffff;
                cursor: pointer;
                font-weight: 700;
                padding: 10px 16px;
            }

            @media print {
                body {
                    margin: 18mm;
                }

                .print-actions {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-actions">
            <button type="button" onclick="window.print()">Simpan PDF</button>
        </div>

        <h1>Histori Peminjaman</h1>
        <div class="meta">
            Periode {{ \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') }}
            sampai sebelum {{ \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y') }}
            @if ($filters['study_program'] !== '')
                | Program Studi: {{ $filters['study_program'] }}
            @endif
            @if ($filters['search'] !== '')
                | Pencarian: {{ $filters['search'] }}
            @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Pinjam</th>
                    <th>Jam Kembali</th>
                    <th>Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>Nomor Loker</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($borrowings as $borrowing)
                    <tr>
                        <td>{{ $borrowing->borrowed_at?->format('d/m/Y') }}</td>
                        <td>{{ $borrowing->borrowed_at?->format('H:i') }}</td>
                        <td>{{ $borrowing->returned_at?->format('H:i') ?? '-' }}</td>
                        <td>{{ $borrowing->student?->name ?? '-' }}</td>
                        <td>{{ $borrowing->student?->study_program ?? '-' }}</td>
                        <td>{{ $borrowing->locker?->code ?? '-' }}</td>
                        <td>{{ $statusLabels[$borrowing->status] ?? ucfirst($borrowing->status) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">Belum ada histori peminjaman untuk filter ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <script>
            window.addEventListener('load', () => window.print());
        </script>
    </body>
</html>
