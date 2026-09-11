<tr>
    <td>
        <select name="items[{{ $index }}][book_id]" class="form-select form-select-sm book-select" required>
            <option value="">-- Pilih Buku --</option>
            @foreach ($books ?? [] as $book)
                <option value="{{ $book->id }}" data-stock="{{ $book->available_stock }}"
                    @selected((string) $selectedBookId === (string) $book->id)>
                    [{{ $book->isbn ?? 'tanpa ISBN' }}] {{ $book->title }} ({{ $book->author }})
                </option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][quantity]" class="form-control form-control-sm qty-input"
               min="1" value="{{ $selectedQty }}" required>
    </td>
    <td class="stock-info">-</td>
    <td>
        <button type="button" class="btn btn-sm btn-outline-danger remove-row" title="Hapus baris" aria-label="Hapus baris"><i data-lucide="x" class="icon-sm"></i></button>
    </td>
</tr>