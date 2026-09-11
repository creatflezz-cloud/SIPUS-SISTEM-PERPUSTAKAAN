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
    <meta name="description" content="SIPUS - Katalog dan Sistem Informasi Perpustakaan.">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/icon-192.svg') }}">
    <title>@yield('title', 'SIPUS') — SIPUS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/sipus.css') }}">
    @stack('styles')
</head>
<body>
<nav class="guest-topbar">
    <a href="{{ route('catalog.index') }}" class="guest-brand text-decoration-none">
        <span class="mark"><i data-lucide="book-open"></i></span>
        <span>SIPUS</span>
    </a>
    <button type="button" class="guest-menu-toggle d-lg-none" id="guestMenuToggle" aria-label="Buka menu" aria-expanded="false">
        <i data-lucide="menu"></i>
    </button>
    <div class="guest-nav" id="guestNav">
        <a href="{{ route('catalog.index') }}" class="btn btn-sm btn-outline-dark"><i data-lucide="library"></i>Katalog</a>
        @auth
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary"><i data-lucide="layout-grid"></i>Dashboard</a>
        @else
            <a href="{{ route('login') }}" class="btn btn-sm btn-outline-dark"><i data-lucide="log-in"></i>Login</a>
        @endauth
    </div>
</nav>
<x-toasts/>
<div class="container-fluid flex-grow-1 d-flex flex-column p-0">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/lucide.min.js') }}"></script>
<script>
    (function () {
        document.querySelectorAll('.toast').forEach(el => {
            setTimeout(() => { const t = bootstrap.Toast.getOrCreateInstance(el, { delay: 4200 }); t.show(); }, 120);
        });
        const nav = document.getElementById('guestNav');
        const navToggle = document.getElementById('guestMenuToggle');
        if (nav && navToggle) {
            const close = () => {
                nav.classList.remove('show');
                navToggle.setAttribute('aria-expanded', 'false');
            };
            navToggle.addEventListener('click', () => {
                const open = nav.classList.toggle('show');
                navToggle.setAttribute('aria-expanded', String(open));
                const icon = navToggle.querySelector('[data-lucide]');
                if (icon) {
                    const isOpen = icon.getAttribute('data-lucide') === 'x';
                    icon.setAttribute('data-lucide', isOpen ? 'menu' : 'x');
                }
                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                    try { window.lucide.createIcons(); } catch (e) {}
                }
            });
            nav.querySelectorAll('a').forEach(a => a.addEventListener('click', close));
        }
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            try { window.lucide.createIcons(); } catch (e) {}
        }
        window.__refreshSipusIcons = function () {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                try { window.lucide.createIcons(); } catch (e) {}
            }
        };
    })();
</script>
@stack('scripts')
</body>
</html>
