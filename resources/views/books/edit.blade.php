@extends('layouts.app')

@section('title', 'Edit Buku')

@section('content')
<x-page-header
    title="Edit Buku"
    eyebrow="Master Data · Buku"
    lead="Perbarui informasi buku atau ganti sampulnya. Sampul lama akan dihapus saat diganti."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Buku', 'url' => route('books.index')],
        ['label' => 'Edit Buku'],
    ]"/>

@include('books._form', ['book' => $book, 'categories' => $categories])
@endsection