@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
<x-page-header
    title="Tambah Anggota"
    eyebrow="Master Data · Anggota"
    lead="Daftarkan anggota baru untuk dapat melakukan transaksi peminjaman."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Anggota', 'url' => route('members.index')],
        ['label' => 'Tambah Anggota'],
    ]">
    <x-slot:actions>
        <a href="{{ route('members.index') }}" class="btn btn-light"><i data-lucide="arrow-left" aria-hidden="true"></i>Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="card-body p-4">
        @include('members._form', ['member' => null])
    </div>
</div>
@endsection