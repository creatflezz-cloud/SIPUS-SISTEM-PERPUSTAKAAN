<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#123B63">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SIPUS">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="description" content="Sistem Informasi Perpustakaan - Kelola peminjaman, pengembalian, dan katalog buku.">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-192.svg') }}">
    <title>@yield('title', 'Dashboard') — SIPUS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sipus.css') }}">
    @stack('styles')
</head>
<body>
@auth
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <div class="mark"><i data-lucide="book-open"></i></div>
        <div>
            <div class="name">SIPUS</div>
            <div class="tag">Perpustakaan</div>
        </div>
        <button type="button" class="sidebar-close d-lg-none" id="sidebarClose" aria-label="Tutup menu" aria-expanded="false">
            <i data-lucide="x" aria-hidden="true"></i>
        </button>
    </div>

    <nav class="sidebar-nav" id="sidebarNav">
        <div class="nav-section">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i data-lucide="layout-grid" aria-hidden="true"></i><span>Dashboard</span>
        </a>

        <div class="nav-section">Katalog</div>
        <a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
            <i data-lucide="library" aria-hidden="true"></i><span>Katalog Buku</span>
        </a>

        <div class="nav-section">Master Data</div>
        <a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
            <i data-lucide="users" aria-hidden="true"></i><span>Anggota</span>
        </a>
        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i data-lucide="tags" aria-hidden="true"></i><span>Kategori Buku</span>
        </a>
        <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}">
            <i data-lucide="book-open" aria-hidden="true"></i><span>Data Buku</span>
        </a>

        <div class="nav-section">Transaksi</div>
        <a href="{{ route('loans.create') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
            <i data-lucide="arrow-right-circle" aria-hidden="true"></i><span>Peminjaman</span>
        </a>
        <a href="{{ route('returns.index') }}" class="nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
            <i data-lucide="arrow-left-circle" aria-hidden="true"></i><span>Pengembalian</span>
        </a>

        <div class="nav-section">Aktivitas</div>
        <a href="{{ route('transactions.index') }}" class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}">
            <i data-lucide="history" aria-hidden="true"></i><span>Riwayat</span>
        </a>

        <div class="nav-section">Laporan</div>
        <a href="{{ route('reports.index') }}" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i data-lucide="file-bar-chart" aria-hidden="true"></i><span>Laporan</span>
        </a>
    </nav>

    <div class="sidebar-foot">
        <div class="user-chip">
            @include('partials.user-avatar', ['user' => auth()->user()])
            <div class="flex-grow-1 min-width-0">
                <div class="name text-truncate">{{ auth()->user()->name }}</div>
                <div class="role">Petugas</div>
            </div>
            <button type="button" class="logout-btn" title="Keluar" data-bs-toggle="modal" data-bs-target="#logoutModal">
                <i data-lucide="log-out" aria-hidden="true"></i>
            </button>
        </div>
    </div>
</aside>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="app-shell">
    <header class="app-topbar">
        <div class="topbar-left">
            <button type="button" class="icon-btn" id="sidebarToggle" aria-label="Buka menu" aria-expanded="false">
                <i data-lucide="menu" aria-hidden="true"></i>
            </button>
            <span class="topbar-date" id="topbarDateTime"><i data-lucide="calendar" aria-hidden="true"></i><span id="topbarDateText"></span> · <i data-lucide="clock" aria-hidden="true"></i><span id="topbarTimeText"></span></span>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php $overdueCount = \App\Models\Loan::query()->where('status', 'terlambat')->count(); @endphp
            <a href="{{ route('returns.index') }}" class="icon-btn" title="Transaksi terlambat" aria-label="Transaksi terlambat">
                <i data-lucide="bell" aria-hidden="true"></i>
                @if ($overdueCount > 0)
                    <span class="dot">{{ min($overdueCount, 99) }}</span>
                @endif
            </a>
            <div class="dropdown">
                <button class="topbar-user" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    @include('partials.user-avatar', ['user' => auth()->user()])
                    <span class="fw-semibold d-none d-sm-inline">{{ auth()->user()->name }}</span>
                    <i data-lucide="chevron-down" class="chev d-none d-sm-inline" aria-hidden="true"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li>
                        <div class="dropdown-header text-truncate">
                            <div class="fw-bold">{{ auth()->user()->name }}</div>
                            <div class="small text-muted">{{ auth()->user()->email }}</div>
                        </div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('dashboard') }}"><i data-lucide="layout-grid" aria-hidden="true"></i>Dashboard</a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('catalog.index') }}"><i data-lucide="library" aria-hidden="true"></i>Katalog Buku</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile') }}"><i data-lucide="circle-user-round" aria-hidden="true"></i>Profil &amp; Pengaturan</a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                            <i data-lucide="log-out" aria-hidden="true"></i>Keluar
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <main class="app-main">
        <x-toasts/>
        @yield('content')
    </main>
</div>

{{-- Mobile Bottom Navigation --}}
<nav class="mobile-bottomnav d-lg-none" id="mobileBottomNav">
    <a href="{{ route('dashboard') }}" class="bnav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i data-lucide="layout-grid" aria-hidden="true"></i>
        <span>Beranda</span>
    </a>
    <a href="{{ route('catalog.index') }}" class="bnav-item {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
        <i data-lucide="library" aria-hidden="true"></i>
        <span>Katalog</span>
    </a>
    <a href="{{ route('loans.create') }}" class="bnav-item {{ request()->routeIs('loans.*') ? 'active' : '' }}">
        <i data-lucide="arrow-right-circle" aria-hidden="true"></i>
        <span>Pinjam</span>
    </a>
    <a href="{{ route('returns.index') }}" class="bnav-item {{ request()->routeIs('returns.*') ? 'active' : '' }}">
        <i data-lucide="arrow-left-circle" aria-hidden="true"></i>
        <span>Kembali</span>
        @if ($overdueCount > 0)
            <span class="bnav-badge">{{ min($overdueCount, 9) }}+</span>
        @endif
    </a>
    <button type="button" class="bnav-item" id="bnavMore" aria-label="Menu lainnya">
        <i data-lucide="more-horizontal" aria-hidden="true"></i>
        <span>Lainnya</span>
    </button>
</nav>

{{-- Mobile More Menu (bottom sheet) --}}
<div class="mobile-bottomsheet d-lg-none" id="mobileMoreSheet">
    <div class="bottomsheet-backdrop" id="bottomsheetBackdrop"></div>
    <div class="bottomsheet-panel">
        <div class="bottomsheet-handle"></div>
        <div class="bottomsheet-header">
            <div class="fw-bold fs-6">Menu Lainnya</div>
            <button type="button" class="bottomsheet-close" id="bottomsheetClose" aria-label="Tutup">
                <i data-lucide="x" aria-hidden="true"></i>
            </button>
        </div>
        <div class="bottomsheet-body">
            <a href="{{ route('members.index') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="users" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Anggota</div>
                    <div class="small text-muted">Kelola data anggota</div>
                </div>
            </a>
            <a href="{{ route('categories.index') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="tags" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Kategori Buku</div>
                    <div class="small text-muted">Kelola kategori</div>
                </div>
            </a>
            <a href="{{ route('books.index') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="book-open" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Data Buku</div>
                    <div class="small text-muted">Kelola koleksi buku</div>
                </div>
            </a>
            <a href="{{ route('transactions.index') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="history" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Riwayat</div>
                    <div class="small text-muted">Riwayat transaksi</div>
                </div>
            </a>
            <a href="{{ route('reports.index') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="file-bar-chart" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Laporan</div>
                    <div class="small text-muted">Lihat laporan</div>
                </div>
            </a>
            <a href="{{ route('profile') }}" class="bottomsheet-item">
                <div class="bs-icon"><i data-lucide="circle-user-round" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold">Profil & Pengaturan</div>
                    <div class="small text-muted">Kelola akun Anda</div>
                </div>
            </a>
            <hr class="my-2">
            <button type="button" class="bottomsheet-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal" id="bnavLogout">
                <div class="bs-icon text-danger"><i data-lucide="log-out" aria-hidden="true"></i></div>
                <div>
                    <div class="fw-semibold text-danger">Keluar</div>
                    <div class="small text-muted">Logout dari akun</div>
                </div>
            </button>
        </div>
    </div>
</div>

{{-- Logout confirmation modal --}}
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" id="logoutModalLabel">Keluar dari Sistem</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body pt-2">
                <p class="mb-0">Apakah Anda yakin ingin keluar dari akun Anda?</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger"><i data-lucide="log-out" aria-hidden="true"></i>Keluar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@else
<x-toasts/>
<div class="w-100">
    @yield('content')
</div>
@endauth

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/lucide.min.js') }}"></script>
<script>
    (function () {
        // Sidebar toggle
        const sidebar = document.getElementById('appSidebar');
        const appShell = document.querySelector('.app-shell');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggle = document.getElementById('sidebarToggle');
        const closeBtn = document.getElementById('sidebarClose');

        const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

        // Re-create lucide icons (handles dynamically added rows / DOM changes)
        function refreshIcons() {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                try { window.lucide.createIcons(); } catch (e) {}
            }
        }

        // Mobile drawer open/close
        function setOpen(open) {
            sidebar.classList.toggle('show', open);
            if (backdrop) backdrop.classList.toggle('show', open);
            toggle.setAttribute('aria-expanded', String(open));
            if (closeBtn) closeBtn.setAttribute('aria-expanded', String(open));
            if (isMobile()) {
                const icon = toggle.querySelector('[data-lucide]');
                if (icon) icon.setAttribute('data-lucide', open ? 'x' : 'menu');
            }
            refreshIcons();
        }

        // Desktop collapse/expand (mini sidebar)
        function setCollapsed(collapsed) {
            sidebar.classList.toggle('collapsed', collapsed);
            if (appShell) appShell.classList.toggle('sidebar-collapsed', collapsed);
            toggle.setAttribute('aria-expanded', String(!collapsed));
            refreshIcons();
        }

        if (toggle && sidebar) {
            toggle.addEventListener('click', () => {
                if (isMobile()) {
                    setOpen(!sidebar.classList.contains('show'));
                } else {
                    setCollapsed(!sidebar.classList.contains('collapsed'));
                }
            });
            if (backdrop) backdrop.addEventListener('click', () => setOpen(false));
            if (closeBtn) closeBtn.addEventListener('click', () => setOpen(false));
            // Close drawer after navigating on mobile
            sidebar.querySelectorAll('.nav-link').forEach(function (link) {
                link.addEventListener('click', () => {
                    if (isMobile()) setOpen(false);
                });
            });
        }

        @auth
        const toasts = document.querySelectorAll('.toast:not(.toast-inline)');
        toasts.forEach((el, i) => {
            setTimeout(() => { const t = bootstrap.Toast.getOrCreateInstance(el, { delay: 4200 }); t.show(); }, 120 * i);
        });
        @endauth

        refreshIcons();
        window.__refreshSipusIcons = refreshIcons;

        // Live date & time (auto-updates every second, changes date automatically at midnight)
        @auth
        (function () {
            var dateEl = document.getElementById('topbarDateText');
            var timeEl = document.getElementById('topbarTimeText');
            if (!dateEl || !timeEl) return;

            var dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            var monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            function pad(n) { return String(n).padStart(2, '0'); }

            function render() {
                var now = new Date();
                var dateStr = dayNames[now.getDay()] + ', ' + pad(now.getDate()) + ' ' + monthNames[now.getMonth()] + ' ' + now.getFullYear();
                var timeStr = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
                dateEl.textContent = dateStr;
                timeEl.textContent = timeStr;
            }

            render();
            setInterval(render, 1000);
        })();
        @endauth
    })();

    // ── Mobile Bottom Sheet Toggle ──
    (function () {
        var moreBtn = document.getElementById('bnavMore');
        var sheet = document.getElementById('mobileMoreSheet');
        var backdrop = document.getElementById('bottomsheetBackdrop');
        var closeBtn = document.getElementById('bottomsheetClose');
        if (!moreBtn || !sheet) return;

        function openSheet() { sheet.classList.add('show'); document.body.style.overflow = 'hidden'; }
        function closeSheet() { sheet.classList.remove('show'); document.body.style.overflow = ''; }

        moreBtn.addEventListener('click', openSheet);
        if (backdrop) backdrop.addEventListener('click', closeSheet);
        if (closeBtn) closeBtn.addEventListener('click', closeSheet);
        sheet.querySelectorAll('.bottomsheet-item').forEach(function (item) {
            item.addEventListener('click', closeSheet);
        });
    })();

    // ── Sidebar Swipe Gesture (mobile) ──
    (function () {
        var sidebar = document.getElementById('appSidebar');
        var backdrop = document.getElementById('sidebarBackdrop');
        if (!sidebar) return;
        var startX = 0, startY = 0, swiping = false, direction = null;
        var threshold = 60;

        sidebar.addEventListener('touchstart', function (e) {
            if (!window.matchMedia('(max-width: 991.98px)').matches) return;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            swiping = true;
            direction = null;
        }, { passive: true });

        sidebar.addEventListener('touchmove', function (e) {
            if (!swiping) return;
            var dx = e.touches[0].clientX - startX;
            var dy = Math.abs(e.touches[0].clientY - startY);
            if (!direction) direction = dy > 30 ? 'vertical' : 'horizontal';
            if (direction === 'vertical') { swiping = false; return; }
            if (sidebar.classList.contains('show') && dx < -threshold) {
                sidebar.classList.remove('show');
                if (backdrop) backdrop.classList.remove('show');
                document.body.style.overflow = '';
                swiping = false;
            }
        }, { passive: true });

        sidebar.addEventListener('touchend', function () { swiping = false; }, { passive: true });
    })();

    // ── Service Worker Registration ──
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js').catch(function () {});
    }
</script>
@stack('scripts')
</body>
</html>
