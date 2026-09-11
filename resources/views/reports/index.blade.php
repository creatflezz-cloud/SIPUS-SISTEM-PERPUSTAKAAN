@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<x-page-header
    title="Laporan"
    eyebrow="Laporan"
    lead="Rekapitulasi koleksi, keanggotaan, dan transaksi perpustakaan. Ekspor hasil ke CSV atau PDF."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Laporan'],
    ]">
    <x-slot:actions>
        <a href="{{ route('reports.export-csv', request()->query()) }}" class="btn btn-outline-secondary">
            <i data-lucide="file-spreadsheet" aria-hidden="true"></i>Export CSV
        </a>
        <a href="{{ route('reports.export-pdf', request()->query()) }}" class="btn btn-outline-secondary">
            <i data-lucide="file-text" aria-hidden="true"></i>Export PDF
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card mb-4">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label" for="date_from">Dari Tanggal</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="date_to">Sampai Tanggal</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="filter" aria-hidden="true"></i>Terapkan Filter</button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary" title="Reset" aria-label="Reset"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i data-lucide="book-open" style="color:var(--sipus-navy)" aria-hidden="true"></i> Laporan Buku</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Total Buku</td><td class="text-end fw-bold">{{ $report['books']['total'] }}</td></tr>
                    <tr><td>Buku Tersedia</td><td class="text-end fw-bold">{{ $report['books']['available'] }}</td></tr>
                    <tr><td>Buku Sedang Dipinjam</td><td class="text-end fw-bold text-warning">{{ $report['books']['borrowed'] }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i data-lucide="users" style="color:var(--sipus-navy)" aria-hidden="true"></i> Laporan Anggota</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Total Anggota</td><td class="text-end fw-bold">{{ $report['members']['total'] }}</td></tr>
                    <tr><td>Anggota Aktif</td><td class="text-end fw-bold text-success">{{ $report['members']['active'] }}</td></tr>
                    <tr><td>Anggota Tidak Aktif</td><td class="text-end fw-bold text-secondary">{{ $report['members']['inactive'] }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><i data-lucide="arrow-left-right" style="color:var(--sipus-navy)" aria-hidden="true"></i> Laporan Transaksi</div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td>Total Peminjaman</td><td class="text-end fw-bold">{{ $report['transactions']['total_loans'] }}</td></tr>
                    <tr><td>Total Pengembalian</td><td class="text-end fw-bold">{{ $report['transactions']['total_returns'] }}</td></tr>
                    <tr><td>Total Keterlambatan</td><td class="text-end fw-bold text-danger">{{ $report['transactions']['total_late'] }}</td></tr>
                    <tr><td>Total Denda</td><td class="text-end fw-bold text-danger">Rp {{ number_format($report['transactions']['total_fine'], 0, ',', '.') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="d-inline-flex align-items-center gap-2"><i data-lucide="list" style="color:var(--sipus-navy)" aria-hidden="true"></i><strong>Daftar Transaksi</strong></span>
            <span class="text-muted small">
                @if ($dateFrom) dari {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d-m-Y') }} @endif
                @if ($dateTo) s/d {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d-m-Y') }} @endif
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Anggota</th>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tanggal Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        <tr>
                            <td data-label="ID">#{{ $loan->id }}</td>
                            <td data-label="Anggota">{{ $loan->member->name }}</td>
                            <td data-label="Buku">
                                @foreach ($loan->items as $item)
                                    <span class="d-block small">{{ $item->book->title }} (x{{ $item->quantity }})</span>
                                @endforeach
                            </td>
                            <td data-label="Tanggal Pinjam">{{ $loan->loan_date?->format('d-m-Y') }}</td>
                            <td data-label="Jatuh Tempo">{{ $loan->due_date?->format('d-m-Y') }}</td>
                            <td data-label="Tanggal Kembali">{{ $loan->return_date?->format('d-m-Y') ?? '-' }}</td>
                            <td data-label="Denda">{{ $loan->total_fine > 0 ? 'Rp '.number_format($loan->total_fine, 0, ',', '.') : '-' }}</td>
                            <td data-label="Status">@include('partials.loan-status', ['status' => $loan->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="file-text" title="Tidak ada transaksi"
                                    description="Tidak ada transaksi pada rentang tanggal tersebut."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection