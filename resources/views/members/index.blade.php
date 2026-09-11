@extends('layouts.app')

@section('title', 'Data Anggota')

@section('content')
<x-page-header
    title="Data Anggota"
    eyebrow="Master Data"
    lead="Kelola data anggota perpustakaan, status keanggotaan, dan riwayat peminjamannya."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Anggota'],
    ]">
    <x-slot:actions>
        <a href="{{ route('members.create') }}" class="btn btn-primary">
            <i data-lucide="plus" aria-hidden="true"></i> Tambah Anggota
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('members.index') }}" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i data-lucide="search" class="icon-sm text-muted" aria-hidden="true"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, nomor anggota, telepon..."
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="tidak_aktif" @selected(request('status') === 'tidak_aktif')>Tidak Aktif</option>
                </select>
            </div>
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-outline-primary"><i data-lucide="filter" aria-hidden="true"></i>Terapkan</button>
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary" title="Reset filter" aria-label="Reset filter"><i data-lucide="x" class="icon-sm" aria-hidden="true"></i></a>
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
                        <th>Anggota</th>
                        <th>Nomor Anggota</th>
                        <th>Jenis Kelamin</th>
                        <th>Telepon</th>
                        <th>Jumlah Peminjaman</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($members as $member)
                        <tr>
                            <td data-label="Anggota">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar d-grid place-items-center flex-shrink-0"
                                          style="width:38px;height:38px;border-radius:11px;background:var(--sipus-navy-tint);color:var(--sipus-navy);font-weight:800">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </span>
                                    <span class="fw-semibold">{{ $member->name }}</span>
                                </div>
                            </td>
                            <td data-label="Nomor Anggota"><code>{{ $member->member_code }}</code></td>
                            <td data-label="Jenis Kelamin">{{ $member->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</td>
                            <td data-label="Telepon">{{ $member->phone ?? '-' }}</td>
                            <td data-label="Jumlah Peminjaman">
                                <span class="badge text-bg-light border">{{ $member->loans()->count() }} transaksi</span>
                            </td>
                            <td data-label="Status">@include('partials.loan-status', ['status' => $member->status])</td>
                            <td data-label="Aksi" class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Aksi" aria-label="Aksi">
                                        <i data-lucide="more-vertical" class="icon-sm" aria-hidden="true"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('members.edit', $member) }}">
                                                <i data-lucide="pencil" aria-hidden="true"></i>Edit
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <button class="dropdown-item text-danger" type="button"
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal-{{ $member->id }}">
                                                <i data-lucide="trash-2" aria-hidden="true"></i>Hapus
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state icon="users" title="Belum ada data anggota"
                                    description="Tidak ada anggota yang cocok dengan pencarian Anda. Tambahkan anggota pertama.">
                                    <x-slot:action>
                                        <a href="{{ route('members.create') }}" class="btn btn-primary btn-sm">
                                            <i data-lucide="plus" aria-hidden="true"></i>Tambah Anggota
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

<div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div class="text-muted small">Menampilkan {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} dari {{ $members->total() }} anggota</div>
    <div>{{ $members->withQueryString()->links() }}</div>
</div>

@foreach ($members as $member)
<div class="modal fade" id="deleteModal-{{ $member->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Hapus Anggota</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus anggota <strong>{{ $member->name }}</strong>?
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('members.destroy', $member) }}">
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