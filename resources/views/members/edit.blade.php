@extends('layouts.app')

@section('title', 'Edit Anggota')

@section('content')
<x-page-header
    title="Edit Anggota"
    eyebrow="Master Data · Anggota"
    lead="Perbarui data keanggotaan sesuai kondisi terkini."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Data Anggota', 'url' => route('members.index')],
        ['label' => 'Edit Anggota'],
    ]">
    <x-slot:actions>
        <a href="{{ route('members.index') }}" class="btn btn-light"><i data-lucide="arrow-left" aria-hidden="true"></i>Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="card-body p-4">
        @include('members._form', ['member' => $member])
    </div>
</div>
@endsection