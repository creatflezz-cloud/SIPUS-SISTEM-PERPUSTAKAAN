@extends('layouts.app')

@section('title', 'Peminjaman')

@section('content')
<x-page-header
    title="Transaksi Peminjaman"
    eyebrow="Transaksi"
    lead="Catat peminjaman buku oleh anggota. Stok tersedia akan berkurang secara otomatis."
    :breadcrumbs="[
        ['label' => 'Beranda', 'url' => route('dashboard')],
        ['label' => 'Peminjaman'],
    ]">
    <x-slot:actions>
        <a href="{{ route('dashboard') }}" class="btn btn-light"><i data-lucide="arrow-left" aria-hidden="true"></i>Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('loans.store') }}" id="loanForm">
            @csrf
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="member_id" class="form-label">Anggota <span class="text-danger">*</span></label>
                    <select name="member_id" id="member_id" class="form-select @error('member_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Anggota (Aktif) --</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected(old('member_id') == $member->id)>
                                [{{ $member->member_code }}] {{ $member->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('member_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="loan_date" class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                    <input type="date" class="form-control @error('loan_date') is-invalid @enderror"
                           id="loan_date" name="loan_date" value="{{ old('loan_date', now()->format('Y-m-d')) }}" required>
                    @error('loan_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label for="loan_days" class="form-label">Lama Pinjam (hari) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control @error('loan_days') is-invalid @enderror"
                           id="loan_days" name="loan_days" min="1" max="30" value="{{ old('loan_days', 7) }}" required>
                    @error('loan_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Jatuh tempo = tanggal pinjam + lama pinjam.</div>
                </div>
            </div>

            <h6 class="fw-bold mb-2"><i data-lucide="library" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i> Daftar Buku</h6>
            <div class="table-responsive mb-3">
                <table class="table table-sm align-middle">
                    <thead>
                        <tr>
                            <th>Buku</th>
                            <th style="width:180px">Jumlah</th>
                            <th style="width:60px">Stok Tersedia</th>
                            <th style="width:60px"></th>
                        </tr>
                    </thead>
                    <tbody id="bookRows">
                        @if (old('items'))
                            @foreach (old('items') as $i => $item)
                                @include('loans.partials.book-row', ['index' => $i, 'selectedBookId' => $item['book_id'] ?? '', 'selectedQty' => $item['quantity'] ?? 1])
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            @error('items')
                <div class="alert alert-danger py-2"><i data-lucide="alert-triangle" aria-hidden="true"></i>{{ $message }}</div>
            @enderror

            <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary" id="addRow">
                    <i data-lucide="plus" aria-hidden="true"></i>Tambah Baris Buku
                </button>
                <button type="submit" class="btn btn-primary"><i data-lucide="check" aria-hidden="true"></i>Simpan Peminjaman</button>
            </div>
        </form>
    </div>
</div>

<div id="bookOptions" class="d-none">
    <option value="">-- Pilih Buku --</option>
    @foreach ($books as $book)
        <option value="{{ $book->id }}" data-stock="{{ $book->available_stock }}">
            [{{ $book->isbn ?? 'tanpa ISBN' }}] {{ $book->title }} ({{ $book->author }})
        </option>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const bookOptions = document.getElementById('bookOptions').innerHTML;
        let rowIndex = document.querySelectorAll('#bookRows tr').length;

        function updateStockInfo(row) {
            const select = row.querySelector('select.book-select');
            const stockCell = row.querySelector('.stock-info');
            const qty = row.querySelector('input.qty-input');
            const selected = select.selectedOptions[0];
            if (selected) {
                const stock = selected.dataset.stock;
                stockCell.textContent = stock;
                if (qty.value > stock) qty.value = stock;
                qty.max = stock;
            } else {
                stockCell.textContent = '-';
                qty.max = '';
            }
        }

        document.getElementById('addRow').addEventListener('click', function () {
            const tbody = document.getElementById('bookRows');
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><select name="items[${rowIndex}][book_id]" class="form-select form-select-sm book-select" required>${bookOptions}</select></td>
                <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty-input" min="1" value="1" required></td>
                <td class="stock-info">-</td>
                <td><button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Hapus baris" aria-label="Hapus baris"><i data-lucide="x" class="icon-sm"></i></button></td>
            `;
            tbody.appendChild(tr);
            tr.querySelector('.book-select').addEventListener('change', () => updateStockInfo(tr));
            tr.querySelector('.remove-row').addEventListener('click', () => tr.remove());
            if (window.__refreshSipusIcons) window.__refreshSipusIcons();
            rowIndex++;
        });

        document.getElementById('bookRows').addEventListener('change', function (e) {
            if (e.target.classList.contains('book-select')) {
                updateStockInfo(e.target.closest('tr'));
            }
        });

        document.querySelectorAll('#bookRows tr').forEach(updateStockInfo);
    })();
</script>
@endpush