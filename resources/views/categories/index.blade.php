@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
<x-page-header
    title="Kategori Buku"
    eyebrow="Master Data"
    lead="Kelompokkan koleksi buku ke dalam kategori agar mudah ditemukan di katalog."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Kategori Buku'],
    ]">
    <x-slot:actions>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
            <i data-lucide="plus" aria-hidden="true"></i> Tambah Kategori
        </button>
    </x-slot:actions>
</x-page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i data-lucide="search" class="icon-sm text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama kategori..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="search" aria-hidden="true"></i>Cari</button>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary" title="Reset" aria-label="Reset"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
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
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Jumlah Buku</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td data-label="ID">{{ $category->id }}</td>
                            <td data-label="Nama">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-grid place-items-center flex-shrink-0" style="width:32px;height:32px;border-radius:9px;background:var(--sipus-navy-tint);color:var(--sipus-navy)">
                                        <i data-lucide="tags" class="icon-sm" aria-hidden="true"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td data-label="Deskripsi" class="text-muted small">{{ \Illuminate\Support\Str::limit($category->description, 50) ?: '-' }}</td>
                            <td data-label="Jumlah Buku">
                                <a href="{{ route('books.index', ['category_id' => $category->id]) }}" class="badge text-bg-primary text-decoration-none" title="Lihat buku dalam kategori ini">
                                    {{ $category->books_count }} buku
                                </a>
                            </td>
                            <td data-label="Aksi" class="text-end">
                                <div class="d-inline-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" title="Edit"
                                            data-bs-toggle="modal" data-bs-target="#editModal-{{ $category->id }}" aria-label="Edit {{ $category->name }}">
                                        <i data-lucide="pencil" class="icon-sm" aria-hidden="true"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                            data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $category->id }}" aria-label="Hapus {{ $category->name }}">
                                        <i data-lucide="trash-2" class="icon-sm" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $categories->withQueryString()->links() }}</div>

<div class="modal fade" id="createModal" tabindex="-1" aria-labelledby="createModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('categories.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="createModalLabel">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required placeholder="cth: Fiksi">
                    </div>
                    <div>
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea name="description" id="description" rows="3" class="form-control"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i data-lucide="check" aria-hidden="true"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@foreach ($categories as $category)
<div class="modal fade" id="editModal-{{ $category->id }}" tabindex="-1" aria-labelledby="editModalLabel-{{ $category->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('categories.update', $category) }}">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel-{{ $category->id }}">Edit Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
                    </div>
                    <div>
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" rows="3" class="form-control">{{ $category->description }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i data-lucide="check" aria-hidden="true"></i>Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal-{{ $category->id }}" tabindex="-1" aria-labelledby="deleteModalLabel-{{ $category->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel-{{ $category->id }}">Hapus Kategori</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus kategori <strong>{{ $category->name }}</strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('categories.destroy', $category) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i data-lucide="trash-2" aria-hidden="true"></i>Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection