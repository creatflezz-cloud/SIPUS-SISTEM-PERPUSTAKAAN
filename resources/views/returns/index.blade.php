@extends('layouts.app')

@section('title', 'Pengembalian')

@section('content')
<x-page-header
    title="Proses Pengembalian"
    eyebrow="Transaksi"
    lead="Proses pengembalian buku dan hitung denda keterlambatan secara otomatis."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Pengembalian'],
    ]"/>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('returns.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i data-lucide="search" class="icon-sm text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama anggota / judul buku / ID transaksi..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="search" aria-hidden="true"></i>Cari</button>
                <a href="{{ route('returns.index') }}" class="btn btn-outline-secondary" title="Reset" aria-label="Reset"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
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
                        <th>Keterlambatan</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($loans as $loan)
                        @php
                            $lateDays = $loan->status === 'terlambat' ? now()->startOfDay()->diffInDays($loan->due_date->startOfDay()) : 0;
                        @endphp
                        <tr>
                            <td data-label="ID">#{{ $loan->id }}</td>
                            <td data-label="Anggota">
                                <strong>{{ $loan->member->name }}</strong><br>
                                <span class="text-muted small">{{ $loan->member->member_code }}</span>
                            </td>
                            <td data-label="Buku">
                                @foreach ($loan->items as $item)
                                    <span class="d-block small">{{ $item->book->title }} (x{{ $item->quantity }})</span>
                                @endforeach
                            </td>
                            <td data-label="Tanggal Pinjam">{{ $loan->loan_date->format('d-m-Y') }}</td>
                            <td data-label="Jatuh Tempo">{{ $loan->due_date->format('d-m-Y') }}</td>
                            <td data-label="Keterlambatan">
                                @if ($lateDays > 0)
                                    <span class="badge text-bg-danger">{{ $lateDays }} hari</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td data-label="Status">@include('partials.loan-status', ['status' => $loan->status])</td>
                            <td data-label="Aksi" class="text-end">
                                <button type="button" class="btn btn-sm btn-success"
                                        data-bs-toggle="modal" data-bs-target="#returnModal-{{ $loan->id }}">
                                    <i data-lucide="arrow-left-circle" class="icon-sm" aria-hidden="true"></i>Proses
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <x-empty-state icon="arrow-left-circle" title="Tidak ada transaksi aktif"
                                    description="Tidak ada transaksi yang sedang dipinjam atau terlambat."/>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $loans->withQueryString()->links() }}</div>

@foreach ($loans as $loan)
    @php
        $lateDays = $loan->status === 'terlambat' ? now()->startOfDay()->diffInDays($loan->due_date->startOfDay()) : 0;
    @endphp
<div class="modal fade" id="returnModal-{{ $loan->id }}" tabindex="-1" aria-labelledby="returnModalLabel-{{ $loan->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('returns.process', $loan) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="returnModalLabel-{{ $loan->id }}">Pengembalian #{{ $loan->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-5">Nama Anggota</dt>
                        <dd class="col-sm-7">{{ $loan->member->name }}</dd>
                        <dt class="col-sm-5">Buku</dt>
                        <dd class="col-sm-7">
                            @foreach ($loan->items as $item)
                                <span class="d-block">{{ $item->book->title }} (x{{ $item->quantity }})</span>
                            @endforeach
                        </dd>
                        <dt class="col-sm-5">Tanggal Pinjam</dt>
                        <dd class="col-sm-7">{{ $loan->loan_date->format('d-m-Y') }}</dd>
                        <dt class="col-sm-5">Jatuh Tempo</dt>
                        <dd class="col-sm-7">{{ $loan->due_date->format('d-m-Y') }}</dd>
                        <dt class="col-sm-5">Keterlambatan</dt>
                        <dd class="col-sm-7">
                            @if ($lateDays > 0)
                                <span class="text-danger fw-semibold"><i data-lucide="alert-triangle" class="icon-sm" aria-hidden="true"></i>{{ $lateDays }} hari</span>
                            @else
                                Tidak terlambat
                            @endif
                        </dd>
                        <dt class="col-sm-5">Perkiraan Denda</dt>
                        <dd class="col-sm-7">
                            @if ($loan->status === 'terlambat')
                                <span class="text-danger fw-semibold">Rp {{ number_format($lateDays * 1000, 0, ',', '.') }}</span>
                            @else
                                Rp 0
                            @endif
                        </dd>
                    </dl>
                    <div class="mt-3">
                        <label for="return_date-{{ $loan->id }}" class="form-label">Tanggal Pengembalian <span class="text-danger">*</span></label>
                        <input type="date" name="return_date" id="return_date-{{ $loan->id }}" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success"><i data-lucide="check" aria-hidden="true"></i>Konfirmasi Kembali</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection