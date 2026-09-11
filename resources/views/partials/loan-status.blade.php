@php
    $badge = match ($status) {
        'dipinjam' => 'text-bg-primary',
        'terlambat' => 'text-bg-danger',
        'dikembalikan' => 'text-bg-success',
        'aktif' => 'text-bg-success',
        'tidak_aktif' => 'text-bg-secondary',
        default => 'text-bg-secondary',
    };
    $label = match ($status) {
        'dipinjam' => 'Dipinjam',
        'terlambat' => 'Terlambat',
        'dikembalikan' => 'Dikembalikan',
        'aktif' => 'Aktif',
        'tidak_aktif' => 'Tidak Aktif',
        default => $status,
    };
@endphp
<span class="badge {{ $badge }}">{{ $label }}</span>