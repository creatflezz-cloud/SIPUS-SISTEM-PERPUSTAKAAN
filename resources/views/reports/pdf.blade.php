<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - SIPUS</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2 { margin: 0 0 4px 0; }
        .muted { color: #666; }
        .summary { width: 100%; border-collapse: collapse; margin: 12px 0; }
        .summary td { padding: 4px 8px; border: 1px solid #ccc; }
        .summary td.label { background: #f1f3f5; width: 40%; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        table.data th { background: #f1f3f5; }
        .text-right { text-align: right; }
        .footer { margin-top: 16px; color: #666; }
    </style>
</head>
<body>
    <h2>SIPUS - Laporan Transaksi</h2>
    <p class="muted">Dibuat pada {{ now()->format('d-m-Y H:i') }}
        @if ($dateFrom) | Dari: {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d-m-Y') }} @endif
        @if ($dateTo) | Sampai: {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d-m-Y') }} @endif
    </p>

    <table class="summary">
        <tr>
            <td class="label">Total Transaksi</td>
            <td>{{ $loans->count() }}</td>
            <td class="label">Total Denda</td>
            <td>Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th>ID</th>
                <th>Anggota</th>
                <th>Buku</th>
                <th>Tanggal Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tanggal Kembali</th>
                <th class="text-right">Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($loans as $loan)
                <tr>
                    <td>{{ $loan->id }}</td>
                    <td>{{ $loan->member->name }}</td>
                    <td>
                        @foreach ($loan->items as $item)
                            {{ $item->book->title }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>{{ $loan->loan_date?->format('d-m-Y') }}</td>
                    <td>{{ $loan->due_date?->format('d-m-Y') }}</td>
                    <td>{{ $loan->return_date?->format('d-m-Y') }}</td>
                    <td class="text-right">{{ $loan->total_fine > 0 ? number_format($loan->total_fine, 0, ',', '.') : '-' }}</td>
                    <td>{{ ucfirst($loan->status) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">Tidak ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer">Dokumen ini dihasilkan otomatis oleh sistem SIPUS.</p>
</body>
</html>