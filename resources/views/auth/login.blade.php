@extends('layouts.guest')

@section('title', 'Login Petugas')

@section('content')
<div class="row g-0 login-split w-100">

    {{-- ─── Left hero panel ─── --}}
    <div class="col-lg-6 login-left d-flex align-items-center position-relative overflow-hidden">
        <img src="/img/login-slide.jpg" alt="Perpustakaan SIPUS" class="login-hero-img">
        <div class="login-hero-overlay"></div>
        <div class="text-white p-5 position-relative login-hero-copy" style="max-width:560px;">
            <div class="chip-pill" style="background:rgba(255,255,255,.1);border-color:rgba(255,255,255,.18)">
                <i data-lucide="book-open"></i> Sistem Informasi Perpustakaan
            </div>
            <h1 class="display-6 fw-bold mt-4 mb-3">Kelola perpustakaan Anda dengan lebih rapi.</h1>
            <p class="mb-4" style="color:#c6d3e4;">SIPUS membantu petugas mengelola anggota, koleksi buku, peminjaman, pengembalian, hingga laporan dalam satu tempat.</p>
            <div class="d-flex flex-column gap-3 mb-4" style="max-width:440px;">
                <div class="d-flex align-items-center gap-3">
                    <span class="d-grid place-items-center flex-shrink-0" style="width:38px;height:38px;border-radius:11px;background:rgba(255,255,255,.12);color:#fff"><i data-lucide="book-open"></i></span>
                    <span class="small" style="flex:1;min-width:0">Katalog buku lengkap dengan sampul dan detail koleksi</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-grid place-items-center flex-shrink-0" style="width:38px;height:38px;border-radius:11px;background:rgba(255,255,255,.12);color:#fff"><i data-lucide="arrow-left-right"></i></span>
                    <span class="small" style="flex:1;min-width:0">Transaksi peminjaman, pengembalian, dan denda otomatis</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="d-grid place-items-center flex-shrink-0" style="width:38px;height:38px;border-radius:11px;background:rgba(255,255,255,.12);color:#fff"><i data-lucide="file-bar-chart"></i></span>
                    <span class="small" style="flex:1;min-width:0">Laporan aktivitas perpustakaan yang siap dicetak</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Right login panel ─── --}}
    <div class="col-lg-6 login-right p-4">
        <div class="login-card-wrap">
            <div class="login-floating-card">

                {{-- Brand --}}
                <div class="text-center">
                    <div class="login-logo mx-auto">
                        <i data-lucide="shield-check"></i>
                    </div>
                </div>

                {{-- Heading --}}
                <h2 class="login-heading text-center">Selamat Datang Kembali</h2>
                <p class="login-subtext text-center mb-4">Masuk dengan akun petugas Anda untuk melanjutkan.</p>

                {{-- Form --}}
                <form method="POST" action="{{ route('login.attempt') }}" id="loginForm" novalidate>
                    @csrf

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="login-label">Alamat Email</label>
                        <div class="login-input-group">
                            <i data-lucide="mail" class="login-input-icon" aria-hidden="true"></i>
                            <input type="email"
                                   class="login-input @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email') }}" required autofocus
                                   placeholder="nama@perpus.test">
                        </div>
                        @error('email')
                            <div class="invalid-feedback d-block mt-1" style="font-size:.75rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="login-label">Password</label>
                        <div class="login-input-group">
                            <i data-lucide="lock" class="login-input-icon" aria-hidden="true"></i>
                            <input type="password"
                                   class="login-input @error('password') is-invalid @enderror"
                                   id="password" name="password"
                                   required placeholder="••••••••">
                            <button type="button"
                                    class="login-toggle-btn"
                                    id="togglePassword"
                                    tabindex="-1"
                                    title="Tampilkan password"
                                    aria-label="Tampilkan password">
                                <i data-lucide="eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block mt-1" style="font-size:.75rem;">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember me + forgot password --}}
                    <div class="d-flex justify-content-between align-items-center mb-4 mt-4">
                        <label class="login-remember">
                            <input type="checkbox" id="remember" name="remember">
                            <span>Ingat saya</span>
                        </label>
                        <a href="#"
                           class="login-forgot-link"
                           data-bs-toggle="modal"
                           data-bs-target="#forgotModal">
                            Lupa password?
                        </a>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="login-btn" id="loginBtn">
                        <i data-lucide="log-in" aria-hidden="true"></i>
                        <span>Masuk</span>
                    </button>
                </form>
            </div>

            {{-- Demo credentials --}}
            @if (app()->environment() !== 'production')
                <div class="login-demo-badge mt-3">
                    Akun demo: <code>admin@sipus.test</code> / <code>password</code>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Forgot password modal ─── --}}
<div class="modal fade" id="forgotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i data-lucide="key-round" aria-hidden="true"></i> Lupa Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p>Reset password dilakukan oleh admin perpustakaan.</p>
                <p class="mb-0 text-muted">Silakan hubungi petugas/admin perpustakaan untuk mendapatkan password baru. Akun tidak dapat di-reset secara mandiri melalui aplikasi ini.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Mengerti</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const toggle = document.getElementById('togglePassword');
        const pwd    = document.getElementById('password');
        if (toggle && pwd) {
            toggle.addEventListener('click', () => {
                const show = pwd.type === 'password';
                pwd.type = show ? 'text' : 'password';
                toggle.querySelector('[data-lucide]').setAttribute('data-lucide', show ? 'eye-off' : 'eye');
                if (window.__refreshSipusIcons) window.__refreshSipusIcons();
            });
        }
        const form = document.getElementById('loginForm');
        const btn  = document.getElementById('loginBtn');
        if (form && btn) {
            form.addEventListener('submit', () => {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Memproses...';
            });
        }
    })();
</script>
@endpush
@endsection
