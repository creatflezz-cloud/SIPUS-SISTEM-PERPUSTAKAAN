@extends('layouts.app')

@section('title', 'Tambah Buku')

@section('content')
<x-page-header
    title="Tambah Buku"
    eyebrow="Master Data · Buku"
    lead="Lengkapi informasi buku dan unggah sampul untuk melengkapi katalog perpustakaan."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Buku', 'url' => route('books.index')],
        ['label' => 'Tambah Buku'],
    ]"/>

@include('books._form', ['book' => null, 'categories' => $categories])
@endsection