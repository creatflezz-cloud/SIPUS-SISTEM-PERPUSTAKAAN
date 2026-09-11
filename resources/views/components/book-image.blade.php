@props(['book' => null, 'class' => ''])

@php
    $src = $book ? $book->coverUrl() : asset('img/book-placeholder.svg');
@endphp

<img src="{{ $src }}" alt="Sampul {{ $book?->title ?? 'buku' }}" loading="lazy" class="book-cover {{ $class }}">
