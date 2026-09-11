@extends('layouts.app')

@section('title', 'Data Buku')

@section('content')
<x-page-header
    title="Data Buku"
    eyebrow="Master Data"
    lead="Kelola seluruh koleksi buku beserta sampul, stok, dan ketersediaannya di perpustakaan."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Buku'],
    ]">
    <x-slot:actions>
        <button type="button" class="btn btn-outline-secondary" id="viewToggle">
            <i data-lucide="layout-grid" id="viewToggleIcon" aria-hidden="true"></i><span id="viewToggleLabel">Mode Grid</span>
        </button>
        <a href="{{ route('books.create') }}" class="btn btn-primary">
            <i data-lucide="plus" aria-hidden="true"></i> Tambah Buku
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('books.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5 col-lg-4">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i data-lucide="search" class="icon-sm text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari judul, penulis, ISBN..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="stock_status" class="form-select">
                    <option value="">Semua Stok</option>
                    <option value="available" @selected(request('stock_status') === 'available')>Tersedia</option>
                    <option value="low" @selected(request('stock_status') === 'low')>Stok Menipis (&le;5)</option>
                    <option value="out" @selected(request('stock_status') === 'out')>Habis</option>
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="filter" aria-hidden="true"></i>Terapkan</button>
                <a href="{{ route('books.index') }}" class="btn btn-outline-secondary" title="Reset filter" aria-label="Reset filter"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- ============ TABLE VIEW ============ --}}
<div class="card books-table" id="booksTable">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sampul</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Kategori</th>
                        <th>Tahun</th>
                        <th>ISBN</th>
                        <th>Rak</th>
                        <th>Stok</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td data-label="Sampul">
                                <a href="{{ route('books.show', $book) }}">
                                    <img src="{{ $book->coverUrl() }}" alt="Sampul {{ $book->title }}"
                                         class="book-thumb" loading="lazy">
                                </a>
                            </td>
                            <td data-label="Judul">
                                <a href="{{ route('books.show', $book) }}" class="fw-semibold text-decoration-none">{{ $book->title }}</a>
                                <div class="small text-muted">{{ $book->publisher ?? '-' }}</div>
                            </td>
                            <td data-label="Penulis">{{ $book->author }}</td>
                            <td data-label="Kategori"><span class="badge text-bg-light border">{{ $book->category?->name ?? '-' }}</span></td>
                            <td data-label="Tahun">{{ $book->publication_year ?? '-' }}</td>
                            <td data-label="ISBN"><code class="small">{{ $book->isbn ?? '-' }}</code></td>
                            <td data-label="Rak">{{ $book->rack_location ?? '-' }}</td>
                            <td data-label="Stok">
                                @if ($book->available_stock == 0)
                                    <span class="badge text-bg-danger">Habis</span>
                                @elseif ($book->available_stock <= 5)
                                    <span class="badge text-bg-warning">{{ $book->available_stock }} tersisa</span>
                                @else
                                    <span class="badge text-bg-success">{{ $book->available_stock }} tersedia</span>
                                @endif
                            </td>
                            <td data-label="Aksi" class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi" aria-label="Aksi">
                                        <i data-lucide="more-vertical" class="icon-sm" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('books.show', $book) }}">
                                                <i data-lucide="eye" aria-hidden="true"></i>Lihat Detail
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('books.edit', $book) }}">
                                                <i data-lucide="pencil" aria-hidden="true"></i>Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('books.edit', $book).'#cover' }}">
                                                <i data-lucide="image" aria-hidden="true"></i>Ubah Sampul
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $book->id }}">
                                                <i data-lucide="trash-2" aria-hidden="true"></i>Hapus
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <x-empty-state icon="book-open" title="Belum ada data buku"
                                    description="Belum ada buku yang cocok dengan pencarian Anda. Tambahkan buku pertama atau ubah filter.">
                                    <x-slot:action>
                                        <a href="{{ route('books.create') }}" class="btn btn-primary btn-sm">
                                            <i data-lucide="plus" aria-hidden="true"></i>Tambah Buku
                                        </a>
                                    </x-slot:action>
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ============ GRID VIEW ============ --}}
<div class="books-grid d-none" id="booksGrid">
    @forelse ($books as $book)
        <div class="book-card">
            <a href="{{ route('books.show', $book) }}" class="cover-wrap d-block">
                <x-book-image :book="$book" class="book-cover"/>
            </a>
            <div class="body">
                <a href="{{ route('books.show', $book) }}" class="text-decoration-none text-reset">
                    <div class="title">{{ $book->title }}</div>
                </a>
                <div class="author">{{ $book->author }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="badge text-bg-light border">{{ $book->category?->name ?? '-' }}</span>
                    @if ($book->available_stock == 0)
                        <span class="badge text-bg-danger">Habis</span>
                    @else
                        <span class="badge text-bg-success">{{ $book->available_stock }} tersedia</span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="card">
            <div class="card-body">
                <x-empty-state icon="book-open" title="Belum ada data buku"
                    description="Belum ada buku yang cocok dengan pencarian Anda.">
                    <x-slot:action>
                        <a href="{{ route('books.create') }}" class="btn btn-primary btn-sm"><i data-lucide="plus" aria-hidden="true"></i>Tambah Buku</a>
                    </x-slot:action>
                </x-empty-state>
            </div>
        </div>
    @endforelse
</div>

<div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="text-muted small">Menampilkan {{ $books->firstItem() ?? 0 }}–{{ $books->lastItem() ?? 0 }} dari {{ $books->total() }} buku</div>
    <div>{{ $books->withQueryString()->links() }}</div>
</div>

@foreach ($books as $book)
<div class="modal fade" id="deleteModal-{{ $book->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Hapus Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus buku <strong>{{ $book->title }}</strong>? Sampul yang diunggah juga akan ikut terhapus.
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('books.destroy', $book) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i data-lucide="trash-2" aria-hidden="true"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    (function () {
        const grid = document.getElementById('booksGrid');
        const table = document.getElementById('booksTable');
        const toggle = document.getElementById('viewToggle');
        const icon = document.getElementById('viewToggleIcon');
        const label = document.getElementById('viewToggleLabel');
        let gridMode = localStorage.getItem('sipus-books-view') === 'grid';

        const apply = () => {
            grid.classList.toggle('d-none', !gridMode);
            table.classList.toggle('d-none', gridMode);
            icon.setAttribute('data-lucide', gridMode ? 'list' : 'layout-grid');
            label.textContent = gridMode ? 'Mode Tabel' : 'Mode Grid';
            if (window.__refreshSipusIcons) window.__refreshSipusIcons();
        };

        if (toggle) {
            toggle.addEventListener('click', () => {
                gridMode = !gridMode;
                localStorage.setItem('sipus-books-view', gridMode ? 'grid' : 'table');
                apply();
            });
        }

        apply();
    })();
</script>
@endpush
@endsection