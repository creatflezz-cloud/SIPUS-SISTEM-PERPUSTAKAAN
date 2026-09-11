@extends('layouts.app')

@section('title', 'Detail Buku')

@section('content')
<x-page-header
    title="Detail Buku"
    eyebrow="Master Data · Buku"
    lead="Informasi lengkap dan ketersediaan koleksi buku ini."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Buku', 'url' => route('books.index')],
        ['label' => $book->title],
    ]">
    <x-slot:actions>
        <a href="{{ route('books.index') }}" class="btn btn-light">
            <i data-lucide="arrow-left" aria-hidden="true"></i>Kembali
        </a>
        <a href="{{ route('books.edit', $book) }}" class="btn btn-outline-secondary">
            <i data-lucide="pencil" aria-hidden="true"></i>Edit
        </a>
        <a href="{{ route('books.edit', $book).'#cover' }}" class="btn btn-outline-primary">
            <i data-lucide="image" aria-hidden="true"></i>Ubah Sampul
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <div class="col-lg-4 col-xl-3">
        <div class="card">
            <div class="card-body p-3 text-center">
                <x-book-image :book="$book" class="book-cover"/>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex flex-wrap align-items-start justify-content-between gap-2 mb-2">
                    <div>
                        <h2 class="h3 fw-bold mb-1">{{ $book->title }}</h2>
                        <div class="text-muted">oleh <span class="fw-semibold text-dark">{{ $book->author }}</span></div>
                    </div>
                    @if ($book->available_stock == 0)
                        <span class="badge text-bg-danger fs-6">Stok Habis</span>
                    @elseif ($book->available_stock <= 5)
                        <span class="badge text-bg-warning fs-6">{{ $book->available_stock }} tersisa</span>
                    @else
                        <span class="badge text-bg-success fs-6">{{ $book->available_stock }} tersedia</span>
                    @endif
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">Kategori</div>
                            <div class="fw-semibold mt-1"><i data-lucide="tags" class="icon-sm" style="color:var(--sipus-navy)" aria-hidden="true"></i>{{ $book->category?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">Penerbit</div>
                            <div class="fw-semibold mt-1">{{ $book->publisher ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">Tahun Terbit</div>
                            <div class="fw-semibold mt-1">{{ $book->publication_year ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">ISBN</div>
                            <div class="fw-semibold mt-1"><code>{{ $book->isbn ?? '-' }}</code></div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">Lokasi / Rak</div>
                            <div class="fw-semibold mt-1">{{ $book->rack_location ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 rounded-3" style="background:var(--sipus-bg)">
                            <div class="text-muted small fw-semibold text-uppercase" style="letter-spacing:.06em">Stok</div>
                            <div class="fw-semibold mt-1">{{ $book->stock }} eksemplar</div>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mt-4 mb-2"><i data-lucide="file-text" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i>Deskripsi</h5>
                @if ($book->description)
                    <p class="text-muted mb-0" style="line-height:1.8">{{ $book->description }}</p>
                @else
                    <p class="text-muted mb-0"><em>Belum ada deskripsi untuk buku ini.</em></p>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($related->isNotEmpty())
    <h5 class="fw-bold mt-4 mb-3"><i data-lucide="library" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i>Buku Lain dalam Kategori {{ $book->category?->name }}</h5>
    <div class="row g-3">
        @foreach ($related as $item)
            <div class="col-6 col-md-4 col-lg-3">
                <div class="book-card">
                    <a href="{{ route('books.show', $item) }}" class="cover-wrap d-block">
                        <x-book-image :book="$item" class="book-cover"/>
                    </a>
                    <div class="body">
                        <a href="{{ route('books.show', $item) }}" class="text-decoration-none text-reset">
                            <div class="title" style="min-height:0">{{ $item->title }}</div>
                        </a>
                        <div class="author">{{ $item->author }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection