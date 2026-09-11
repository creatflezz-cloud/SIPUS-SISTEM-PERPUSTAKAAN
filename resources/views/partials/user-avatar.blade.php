@php
    $user = $user ?? null;
    $class = $class ?? '';
@endphp

<span class="avatar {{ $class }}">
    @if ($user && $user->photoUrl())
        <img src="{{ $user->photoUrl() }}" alt="{{ $user->name }}">
    @else
        {{ strtoupper(substr($user?->name ?? '', 0, 1)) }}
    @endif
</span>