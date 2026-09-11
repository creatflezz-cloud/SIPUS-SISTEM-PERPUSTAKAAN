@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header
    title="Halo, {{ auth()->user()->name }}"
    eyebrow="Dashboard"
    lead="Selamat datang kembali. Berikut ringkasan aktivitas perpustakaan hari ini."
    >
    <x-slot:actions>
        <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary">
            <i data-lucide="library" aria-hidden="true"></i>Lihat Katalog
        </a>
        <a href="{{ route('loans.create') }}" class="btn btn-primary">
            <i data-lucide="plus" aria-hidden="true"></i>Transaksi Peminjaman
        </a>
    </x-slot:actions>
</x-page-header>

{{-- Primary stats --}}
<div class="row g-2 g-sm-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="book-open" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $totalBooks }}">0</h3>
            <div class="stat-label">Judul Koleksi Buku</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="users" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $totalMembers }}">0</h3>
            <div class="stat-label">Anggota Terdaftar</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="package" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $availableBooks }}">0</h3>
            <div class="stat-label">Eksemplar Tersedia</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="arrow-left-right" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $borrowedBooks }}">0</h3>
            <div class="stat-label">Sedang Dipinjam</div>
        </div>
    </div>
</div>

{{-- Secondary stats --}}
<div class="row g-2 g-sm-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="calendar-plus" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $todayLoans }}">0</h3>
            <div class="stat-label">Peminjaman Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="circle-check" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $todayReturns }}">0</h3>
            <div class="stat-label">Pengembalian Hari Ini</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="clock-alert" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $lateLoans }}">0</h3>
            <div class="stat-label">Transaksi Terlambat</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="card stat-card p-3 p-sm-4">
            <div class="stat-icon"><i data-lucide="banknote" aria-hidden="true"></i></div>
            <h3 class="stat-value" data-counter data-target="{{ $totalFine }}" data-format="currency" data-prefix="Rp">Rp0</h3>
            <div class="stat-label">Denda Terkumpul</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Chart --}}
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span class="d-inline-flex align-items-center gap-2"><i data-lucide="bar-chart-3" aria-hidden="true"></i>Peminjaman 6 Bulan Terakhir</span>
                <span class="badge chart-badge">{{ $activeLoans }} transaksi aktif</span>
            </div>
            <div class="card-body">
                @php $max = max(1, max(array_column($months, 'count'))); @endphp
                <div class="d-flex align-items-end justify-content-between gap-2 chart-area" style="height:240px">
                    @foreach ($months as $month)
                        @php
                            $value = $month['count'];
                            $pct = ($value / $max) * 170;
                            $isActive = $value > 0;
                        @endphp
                        <div class="chart-col">
                            <span class="chart-value">{{ $value }}</span>
                            <div class="chart-bar {{ $isActive ? 'chart-bar-active' : 'chart-bar-empty' }}"
                                 style="height:{{ max(6, $pct) }}px"
                                 data-label="{{ $month['label'] }}" data-count="{{ $value }}"
                                 role="img" aria-label="{{ $month['label'] }}: {{ $value }} peminjaman">
                                <span class="chart-tooltip">{{ $month['label'] }} · {{ $value }} peminjaman</span>
                            </div>
                            <span class="chart-label">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Popular books --}}
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><i data-lucide="trending-up" aria-hidden="true"></i> Buku Terpopuler</div>
            <div class="card-body p-2">
                <ol class="list-group list-group-flush">
                    @forelse ($popularBooks as $i => $item)
                        <li class="list-group-item d-flex align-items-center gap-3 px-2">
                            <span class="fw-bold fs-6" style="color:var(--sipus-navy);width:22px;min-width:22px;text-align:center">{{ $i + 1 }}</span>
                            <a href="{{ route('books.show', $item->id) }}" class="d-block flex-shrink-0">
                                <img src="{{ $item->photo ? asset('storage/'.$item->photo) : asset('img/book-placeholder.svg') }}"
                                     class="book-thumb" alt="{{ $item->title }}" style="width:34px;height:46px" loading="lazy">
                            </a>
                            <div class="flex-grow-1 min-width-0">
                                <a href="{{ route('books.show', $item->id) }}" class="fw-semibold text-decoration-none text-dark text-truncate d-block small">{{ $item->title }}</a>
                                <div class="small text-muted">{{ $item->author }}</div>
                            </div>
                            <span class="badge text-bg-light border flex-shrink-0">{{ $item->total_borrowed }}×</span>
                        </li>
                    @empty
                        <li class="list-group-item px-2"><x-empty-state icon="trending-up" title="Belum ada data peminjaman"/></li>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Recent activity --}}
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-inline-flex align-items-center gap-2"><i data-lucide="history" aria-hidden="true"></i>Aktivitas Terbaru</span>
                <a href="{{ route('transactions.index') }}" class="btn btn-sm btn-light">
                    <i data-lucide="arrow-right" aria-hidden="true"></i>Lihat Semua
                </a>
            </div>
            <div class="card-body p-0">
                @forelse ($recentLoans as $loan)
                    <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom"
                         style="border-color:var(--sipus-border-soft)">
                        @php $cover = $loan->items->first()?->book; @endphp
                        <div class="flex-shrink-0 d-flex align-items-center justify-content-center"
                             style="width:42px;height:42px;border-radius:12px;background:var(--sipus-navy-tint)">
                            <i data-lucide="{{ $loan->status == 'dikembalikan' ? 'arrow-left-circle' : 'arrow-right-circle' }}" class="{{ $loan->status == 'dikembalikan' ? 'text-success' : '' }}" aria-hidden="true"></i>
                        </div>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold small text-truncate">
                                {{ $loan->member?->name ?? 'Anggota' }} meminjam
                                {{ $loan->items->pluck('book.title')->join(', ', ' dan ') }}
                            </div>
                            <div class="small text-muted">
                                <span class="text-nowrap"><i data-lucide="calendar" aria-hidden="true"></i>{{ $loan->loan_date->format('d M Y') }}</span>
                                @if ($loan->total_fine > 0)
                                    · <span class="text-danger fw-semibold">denda Rp{{ number_format($loan->total_fine, 0, ',', '.') }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="flex-shrink-0">@include('partials.loan-status', ['status' => $loan->status])</span>
                    </div>
                @empty
                    <x-empty-state icon="history" title="Belum ada aktivitas"
                        description="Transaksi peminjaman dan pengembalian akan muncul di sini."/>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Right column: low stock + newest members --}}
    <div class="col-lg-5">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span class="d-inline-flex align-items-center gap-2"><i data-lucide="alert-triangle" style="color:var(--sipus-warning)" aria-hidden="true"></i>Stok Menipis</span>
                <a href="{{ route('books.index', ['stock_status' => 'low']) }}" class="btn btn-sm btn-light">
                    <i data-lucide="settings" aria-hidden="true"></i>Kelola
                </a>
            </div>
            <div class="card-body p-0">
                @forelse ($lowStockBooks as $book)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="border-color:var(--sipus-border-soft)">
                        <img src="{{ $book->coverUrl() }}" class="book-thumb" style="width:30px;height:40px" alt="{{ $book->title }}" loading="lazy">
                        <a href="{{ route('books.show', $book) }}" class="fw-semibold small text-decoration-none text-truncate flex-grow-1">{{ $book->title }}</a>
                        @if ($book->available_stock == 0)
                            <span class="badge text-bg-danger">Habis</span>
                        @else
                            <span class="badge text-bg-warning">{{ $book->available_stock }} sisa</span>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="circle-check" title="Semua stok aman"/>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-header"><i data-lucide="user-plus" aria-hidden="true"></i> Anggota Terbaru</div>
            <div class="card-body p-0">
                @forelse ($newestMembers as $member)
                    <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom" style="border-color:var(--sipus-border-soft)">
                        <span class="avatar d-grid place-items-center flex-shrink-0"
                              style="width:36px;height:36px;border-radius:11px;background:var(--sipus-navy-tint);color:var(--sipus-navy);font-weight:700">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </span>
                        <div class="flex-grow-1 min-width-0">
                            <div class="fw-semibold small text-truncate">{{ $member->name }}</div>
                            <div class="small text-muted text-nowrap"><code>{{ $member->member_code }}</code></div>
                        </div>
                        <span class="badge text-bg-{{ $member->isActive() ? 'success' : 'secondary' }}">{{ $member->isActive() ? 'Aktif' : 'Nonaktif' }}</span>
                    </div>
                @empty
                    <x-empty-state icon="users" title="Belum ada anggota"/>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        var counters = document.querySelectorAll('[data-counter]');
        if (!counters.length) return;

        var duration = 1000;
        var easeOutCubic = function (t) { return 1 - Math.pow(1 - t, 3); };

        function formatNumber(value, currency, prefix) {
            var formatted = Math.round(value).toLocaleString('id-ID');
            return currency ? (prefix || '') + formatted : formatted;
        }

        counters.forEach(function (el) {
            var target = parseFloat(el.getAttribute('data-target')) || 0;
            var currency = el.getAttribute('data-format') === 'currency';
            var prefix = el.getAttribute('data-prefix') || '';
            var start = null;

            function tick(ts) {
                if (start === null) start = ts;
                var progress = Math.min((ts - start) / duration, 1);
                var value = target * easeOutCubic(progress);
                el.textContent = formatNumber(value, currency, prefix);
                if (progress < 1) {
                    requestAnimationFrame(tick);
                } else {
                    el.textContent = formatNumber(target, currency, prefix);
                }
            }

            requestAnimationFrame(tick);
        });
    })();
</script>
@endpush
