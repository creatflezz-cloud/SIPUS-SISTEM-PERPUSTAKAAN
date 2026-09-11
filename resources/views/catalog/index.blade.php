@extends('layouts.guest')

@section('title', 'Katalog Buku')

@section('content')
<div class="catalog-hero text-white">
    <div class="container py-3 py-md-5">
        <div class="row align-items-center g-3 g-md-4">
            <div class="col-lg-6">
                <span class="chip-pill"><i data-lucide="book-open" aria-hidden="true"></i> Katalog Perpustakaan</span>
                <h1 class="display-6 fw-bolder mt-2 mt-md-3 mb-2 text-white">Telusuri Koleksi Buku</h1>
                <p class="mb-0 d-none d-md-block" style="color:#c3d0e2;max-width:520px">
                    Temukan buku favorit Anda. Setiap koleksi dilengkapi sampul, informasi lengkap, dan status ketersediaan terbaru dari database.
                </p>
            </div>
            <div class="col-lg-6">
                <form method="GET" action="{{ route('catalog.index') }}" class="row g-2">
                    <div class="col-12">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-0"><i data-lucide="search" aria-hidden="true"></i></span>
                            <input type="text" name="search" class="form-control border-0 shadow-none" placeholder="Cari judul, penulis, ISBN, penerbit..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-12 d-flex flex-wrap gap-2 align-items-center">
                        <select name="category_id" class="form-select bg-white catalog-hero-select">
                            <option value="">Semua Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                    {{ $category->name }} ({{ $category->books_count }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-check form-check-inline text-white m-0">
                            <input type="checkbox" class="form-check-input" name="available" value="1" id="onlyAvailable"
                                   @checked(request()->boolean('available'))>
                            <label class="form-check-label small" for="onlyAvailable">Tersedia saja</label>
                        </div>
                        <button type="submit" class="btn btn-primary ms-auto"><i data-lucide="search" aria-hidden="true"></i>Terapkan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="container py-3 py-md-5 flex-grow-1">
    @if (request()->hasAny(['search', 'category_id', 'available']))
        <div class="d-flex justify-content-between align-items-center mb-4 gap-2 flex-wrap">
            <div class="small text-muted">
                Hasil {{ $books->total() }} buku untuk
                @if (request('search')) kata kunci "<strong class="text-dark">{{ request('search') }}</strong>"@endif
                @if (request('category_id')) di kategori <strong class="text-dark">{{ $categories->firstWhere('id', request('category_id'))?->name }}</strong>@endif
                @if (request()->boolean('available')) · <strong class="text-dark">tersedia</strong>@endif
            </div>
            <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-outline-secondary">
                <i data-lucide="x" class="icon-sm" aria-hidden="true"></i>Reset
            </a>
        </div>
    @else
        <div class="row g-2 mb-4">
            @foreach ($categories as $cat)
                <div class="col-auto">
                    <a href="{{ route('catalog.index', ['category_id' => $cat->id]) }}"
                       class="btn btn-sm rounded-pill {{ request('category_id') == $cat->id ? 'btn-primary' : 'btn-light border' }}">
                        {{ $cat->name }} <span class="opacity-75">· {{ $cat->books_count }}</span>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3">
    @forelse ($books as $book)
        <div class="col-6 col-md-3">
            <a href="{{ route('catalog.show', $book) }}" class="text-decoration-none">
                <div class="catalog-card">
                    <div class="cover-wrap">
                        <x-book-image :book="$book" class="book-cover"/>
                    </div>
                    <div class="p-3">
                        <div class="title text-dark">{{ $book->title }}</div>
                        <div class="author mt-1">{{ $book->author }}</div>
                        <div class="d-flex align-items-center justify-content-between mt-2">
                            <span class="badge text-bg-light border text-dark">{{ $book->category?->name }}</span>
                            @if ($book->available_stock == 0)
                                <span class="badge text-bg-danger">Habis</span>
                            @elseif ($book->available_stock <= 5)
                                <span class="badge text-bg-warning">{{ $book->available_stock }} tersisa</span>
                            @else
                                <span class="badge text-bg-success">{{ $book->available_stock }} tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>
            </a>
        </div>
    @empty
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <x-empty-state icon="search" title="Buku tidak ditemukan"
                        description="Tidak ada buku yang cocok dengan pencarian Anda. Coba kata kunci lain."/>
                </div>
            </div>
        </div>
    @endforelse
    </div>

    <div class="mt-4 d-flex justify-content-center">
        {{ $books->links() }}
    </div>
</div>

<div style="background:#101d33;color:#8fa1bd">
    <div class="container py-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="small">
            <i data-lucide="book-open" aria-hidden="true"></i><strong class="text-white">SIPUS</strong> — Sistem Informasi Perpustakaan
        </div>
        <div class="small">Data koleksi dimuat langsung dari perpustakaan.</div>
    </div>
</div>
@endsection