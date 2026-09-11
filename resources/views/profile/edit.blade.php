@extends('layouts.app')

@section('title', 'Profil & Pengaturan')

@section('content')
<x-page-header
    title="Profil & Pengaturan"
    eyebrow="Akun"
    lead="Kelola profil pengelola perpustakaan, foto, dan data anggota peminjam."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Profil & Pengaturan'],
    ]">
</x-page-header>

<form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="row g-3">
    @csrf
    @method('PUT')

    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body d-flex flex-column align-items-center text-center">
                <div class="profile-avatar mb-3" id="avatarWrap">
                    @if ($user->photoUrl())
                        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }} Foto profil" id="photoPreview">
                    @else
                        <span id="photoPreview" class="profile-avatar-initial">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                    @endif
                </div>

                <h4 class="fw-bold mb-0">{{ $user->name }}</h4>
                <div class="text-muted small">{{ $user->email }}</div>
                <span class="badge text-bg-light border mt-2"><i data-lucide="shield-check" class="icon-sm d-inline-block align-text-bottom" aria-hidden="true"></i> Petugas Perpustakaan</span>
                <span class="badge text-bg-light border mt-1">Terdaftar sejak {{ $user->created_at?->translatedFormat('d F Y') }}</span>

                <p class="profile-tagline mb-0">
                    “Sistem Informasi Perpustakaan — kelola koleksi buku, data anggota, dan seluruh transaksi peminjaman dalam satu aplikasi.”
                </p>

                <hr class="w-100 my-3">

                <div class="w-100 text-start small">
                    <label for="photo" class="form-label fw-semibold d-flex align-items-center gap-2">
                        <i data-lucide="camera" class="icon-sm text-muted" aria-hidden="true"></i>Ganti Foto Profil
                    </label>
                    <input type="file" class="form-control @error('photo') is-invalid @enderror"
                           id="photo" name="photo" accept="image/*">
                    @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Format JPG, PNG, atau WebP. Maksimal 2 MB.</div>
                </div>

                @if ($user->photo)
                    <div class="form-check align-self-start mt-2">
                        <input class="form-check-input" type="checkbox" name="remove_photo" value="1" id="removePhoto">
                        <label class="form-check-label small text-danger" for="removePhoto">Hapus foto profil</label>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-transparent fw-semibold border-bottom d-flex align-items-center gap-2">
                <i data-lucide="user-cog" aria-hidden="true"></i> Data Akun
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                               id="password" name="password" autocomplete="new-password" placeholder="Kosongkan jika tidak diganti">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="password_confirmation"
                               name="password_confirmation" autocomplete="new-password" placeholder="Ulangi password baru">
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i data-lucide="check" aria-hidden="true"></i>Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="card mt-3">
    <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="fw-semibold d-flex align-items-center gap-2">
            <i data-lucide="users" aria-hidden="true"></i> Nama Peminjam Terbaru
        </div>
        <a href="{{ route('members.index') }}" class="btn btn-sm btn-outline-primary">
            <i data-lucide="settings-2" aria-hidden="true"></i> Kelola Anggota
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Nomor Anggota</th>
                        <th>Telepon</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentMembers as $member)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="avatar d-grid place-items-center flex-shrink-0"
                                          style="width:36px;height:36px;border-radius:10px;background:var(--sipus-navy-tint);color:var(--sipus-navy);font-weight:800">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </span>
                                    <span class="fw-semibold">{{ $member->name }}</span>
                                </div>
                            </td>
                            <td><code>{{ $member->member_code }}</code></td>
                            <td>{{ $member->phone ?? '-' }}</td>
                            <td>@include('partials.loan-status', ['status' => $member->status])</td>
                            <td class="text-end">
                                <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-outline-secondary" title="Edit anggota" aria-label="Edit anggota">
                                    <i data-lucide="pencil" class="icon-sm" aria-hidden="true"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="text-center text-muted py-5">
                                    <i data-lucide="users" class="icon-lg mb-2 d-block mx-auto opacity-50" aria-hidden="true"></i>
                                    Belum ada anggota terdaftar.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('photo')?.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const wrap = document.getElementById('avatarWrap');
        if (!wrap) return;
        const url = URL.createObjectURL(file);
        const img = document.createElement('img');
        img.src = url;
        img.id = 'photoPreview';
        img.alt = 'Pratinjau foto profil';
        wrap.innerHTML = '';
        wrap.appendChild(img);
    });
</script>
@endpush