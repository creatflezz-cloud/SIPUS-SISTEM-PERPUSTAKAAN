@extends('layouts.guest')

@section('title', $book->title)

@section('content')
<div class="container py-3 py-md-5 flex-grow-1" style="max-width:1080px">
    <nav class="mb-4 small">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}" class="text-decoration-none">Katalog</a></li>
            <li class="breadcrumb-item"><a href="{{ route('catalog.index', ['category_id' => $book->category_id]) }}" class="text-decoration-none">{{ $book->category?->name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $book->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-md-5 col-lg-4">
            <div class="card p-3">
                <x-book-image :book="$book" class="book-cover"/>
            </div>
            <div class="d-grid gap-2 mt-3">
                @if ($book->available_stock > 0)
                    <button type="button" class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#borrowModal">
                        <i data-lucide="plus-circle" aria-hidden="true"></i>Ajukan Peminjaman
                    </button>
                @else
                    <button type="button" class="btn btn-light btn-lg disabled">Stok sedang habis</button>
                @endif
                <a href="{{ route('catalog.index') }}" class="btn btn-outline-secondary"><i data-lucide="arrow-left" aria-hidden="true"></i>Kembali ke Katalog</a>
            </div>
        </div>

        <div class="col-md-7 col-lg-8">
            <span class="badge text-bg-primary fs-6 mb-2">{{ $book->category?->name }}</span>
            <h1 class="fw-bolder mb-1">{{ $book->title }}</h1>
            <p class="text-muted mb-3">oleh <strong class="text-dark">{{ $book->author }}</strong></p>

            @if ($book->available_stock == 0)
                <div class="alert alert-danger"><i data-lucide="circle-x" aria-hidden="true"></i>Koleksi ini sedang tidak tersedia untuk dipinjam.</div>
            @elseif ($book->available_stock <= 5)
                <div class="alert alert-warning"><i data-lucide="triangle-alert" aria-hidden="true"></i>Sisa {{ $book->available_stock }} eksemplar — segera pinjam sebelum kehabisan.</div>
            @else
                <div class="alert alert-success"><i data-lucide="circle-check" aria-hidden="true"></i>{{ $book->available_stock }} eksemplar tersedia untuk dipinjam.</div>
            @endif

            <div class="row g-2 mb-4">
                <div class="col-6">
                    <div class="p-3 rounded-3 h-100 info-box">
                        <div class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.06em">ISBN</div>
                        <div class="fw-semibold mt-1"><code>{{ $book->isbn ?? '-' }}</code></div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 h-100 info-box">
                        <div class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.06em">Penerbit</div>
                        <div class="fw-semibold mt-1">{{ $book->publisher ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 h-100 info-box">
                        <div class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.06em">Tahun Terbit</div>
                        <div class="fw-semibold mt-1">{{ $book->publication_year ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-3 h-100 info-box">
                        <div class="small text-muted fw-semibold text-uppercase" style="letter-spacing:.06em">Lokasi</div>
                        <div class="fw-semibold mt-1">{{ $book->rack_location ?? '-' }}</div>
                    </div>
                </div>
            </div>

            <h5 class="fw-bold mb-2"><i data-lucide="file-text" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i>Deskripsi</h5>
            @if ($book->description)
                <p class="text-muted" style="line-height:1.9">{{ $book->description }}</p>
            @else
                <p class="text-muted"><em>Belum ada deskripsi untuk buku ini.</em></p>
            @endif
        </div>
    </div>

    @if ($related->isNotEmpty())
        <h5 class="fw-bold mt-5 mb-3"><i data-lucide="library" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i>Lihat Juga di {{ $book->category?->name }}</h5>
        <div class="row g-3">
            @foreach ($related as $item)
                <div class="col-6 col-md-3">
                    <a href="{{ route('catalog.show', $item) }}" class="text-decoration-none">
                        <div class="catalog-card">
                            <div class="cover-wrap">
                                <x-book-image :book="$item" class="book-cover"/>
                            </div>
                            <div class="p-3">
                                <div class="title text-dark">{{ $item->title }}</div>
                                <div class="author mt-1">{{ $item->author }}</div>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>

<div class="modal fade" id="borrowModal" tabindex="-1" aria-labelledby="borrowModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="borrowModalLabel"><i data-lucide="plus-circle" style="color:var(--sipus-navy)" aria-hidden="true"></i>Pengajuan Peminjaman</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>
                    Untuk meminjam <strong>{{ $book->title }}</strong>, silakan menghubungi petugas perpustakaan
                    atau mengajukan secara langsung melalui layanan sirkulasi.
                </p>
                @auth
                    <p class="mb-0"><a href="{{ route('loans.create') }}" class="btn btn-primary w-100"><i data-lucide="arrow-right-circle" aria-hidden="true"></i>Buat Transaksi Peminjaman</a></p>
                @else
                    <p class="mb-0 small text-muted">Anda dapat meminjam langsung di perpustakaan dengan menunjukkan kartu anggota.</p>
                @endauth
            </div>
        </div>
    </div>
</div>

<div style="background:#101d33;color:#8fa1bd">
    <div class="container py-4">
        <div class="small">
            <i data-lucide="book-open" aria-hidden="true"></i><strong class="text-white">SIPUS</strong> — Sistem Informasi Perpustakaan
        </div>
    </div>
</div>
@endsection