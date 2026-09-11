@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<x-page-header
    title="Riwayat Transaksi"
    eyebrow="Aktivitas"
    lead="Telusuri seluruh aktivitas peminjaman dan pengembalian beserta denda keterlambatannya."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Riwayat Transaksi'],
    ]"/>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('transactions.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i data-lucide="search" class="icon-sm text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama anggota / judul buku..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" @selected(request('status') === 'dipinjam')>Dipinjam</option>
                    <option value="terlambat" @selected(request('status') === 'terlambat')>Terlambat</option>
                    <option value="dikembalikan" @selected(request('status') === 'dikembalikan')>Dikembalikan</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Tanggal mulai">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}" title="Tanggal selesai">
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="search" aria-hidden="true"></i>Cari</button>
                <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary" title="Reset" aria-label="Reset"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="card">
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
                            <td data-label="Tanggal Pinjam">{{ $loan->loan_date->format('d-m-Y') }}</td>
                            <td data-label="Jatuh Tempo">{{ $loan->due_date->format('d-m-Y') }}</td>
                            <td data-label="Tanggal Kembali">{{ $loan->return_date?->format('d-m-Y') ?? '-' }}</td>
                            <td data-label="Denda">
                                @if ((float) $loan->total_fine > 0)
                                    <span class="text-danger fw-semibold">Rp {{ number_format($loan->total_fine, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td data-label="Status">@include('partials.loan-status', ['status' => $loan->status])</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="history" title="Tidak ada transaksi"
                                    description="Tidak ada transaksi yang cocok dengan filter Anda."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $loans->links() }}</div>
@endsection