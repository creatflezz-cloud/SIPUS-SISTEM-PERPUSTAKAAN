@php
    $isEdit = isset($book) && $book !== null;
    $book = $book ?? null;
@endphp

<form method="POST" action="{{ $isEdit ? route('books.update', $book) : route('books.store') }}"
      enctype="multipart/form-data" class="row g-3">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="col-lg-4 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-2 mb-3" id="cover">
                    <i data-lucide="image" class="icon-md" style="color:var(--sipus-navy)" aria-hidden="true"></i>
                    <span class="fw-bold">Sampul Buku</span>
                    <span class="badge text-bg-primary ms-auto">Wajib</span>
                </div>

                <div class="text-center mb-3">
                    <img src="{{ $isEdit && $book->photo ? $book->coverUrl() : asset('img/book-placeholder.svg') }}"
                         id="coverPreview"
                         alt="Pratinjau sampul buku"
                         class="book-cover mx-auto" style="max-width:170px;">
                </div>

                @if ($isEdit && $book->photo)
                    <input type="hidden" id="removePhotoFlag" name="remove_photo" value="0">

                    <div class="d-grid gap-2 mb-3 cover-action-group">
                        <button type="button" class="btn btn-primary w-100" id="btnGantiFoto">
                            <i data-lucide="refresh-cw" aria-hidden="true"></i>Ganti Foto
                        </button>
                        <button type="button" class="btn btn-outline-danger w-100" id="btnHapusFoto">
                            <i data-lucide="trash-2" aria-hidden="true"></i>Hapus Foto
                        </button>
                    </div>
                @elseif (!$isEdit)
                    <div class="d-grid mb-3">
                        <button type="button" class="btn btn-primary w-100" id="btnPilihFoto">
                            <i data-lucide="upload" aria-hidden="true"></i>Pilih Foto
                        </button>
                    </div>
                @endif

                <input type="file" id="photo" name="photo"
                       class="form-control cover-file-input @error('photo') is-invalid @enderror"
                       accept="image/jpeg,image/png,image/webp" style="display:none;">
                @error('photo')<div class="invalid-feedback">{{ $message }}</div>@enderror

                <p class="text-muted small mt-2 mb-0">
                    <i data-lucide="info" class="icon-xs" aria-hidden="true"></i>Format JPG, PNG, atau WebP. Maksimal 2 MB.
                </p>
            </div>
        </div>
    </div>

    <div class="col-lg-8 col-xl-9">
        <div class="card">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label for="title" class="form-label">Judul Buku <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                               id="title" name="title" value="{{ old('title', $book->title ?? '') }}" required>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="category_id" class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" id="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Pilih --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $book->category_id ?? '') == $category->id)>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="author" class="form-label">Penulis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('author') is-invalid @enderror"
                               id="author" name="author" value="{{ old('author', $book->author ?? '') }}" required>
                        @error('author')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="isbn" class="form-label">ISBN</label>
                        <input type="text" class="form-control @error('isbn') is-invalid @enderror"
                               id="isbn" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}" placeholder="cth: 9780131103627">
                        @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="publisher" class="form-label">Penerbit</label>
                        <input type="text" class="form-control" id="publisher" name="publisher" value="{{ old('publisher', $book->publisher ?? '') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="publication_year" class="form-label">Tahun Terbit</label>
                        <input type="number" class="form-control @error('publication_year') is-invalid @enderror"
                               id="publication_year" name="publication_year" min="1900" max="{{ now()->year }}"
                               value="{{ old('publication_year', $book->publication_year ?? '') }}" placeholder="{{ now()->year }}">
                        @error('publication_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label for="stock" class="form-label">Jumlah Stok <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock') is-invalid @enderror"
                               id="stock" name="stock" min="0" value="{{ old('stock', $book->stock ?? 1) }}" required>
                        @error('stock')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="rack_location" class="form-label">Lokasi / Rak</label>
                        <input type="text" class="form-control" id="rack_location" name="rack_location"
                               value="{{ old('rack_location', $book->rack_location ?? '') }}" placeholder="cth: Rak A-1">
                    </div>
                    <div class="col-md-6">
                        <label for="description" class="form-label">Deskripsi</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description"
                                  name="description" rows="4" placeholder="Sinopsis atau ringkasan buku...">{{ old('description', $book->description ?? '') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
        <a href="{{ route('books.index') }}" class="btn btn-outline-secondary"><i data-lucide="x" aria-hidden="true"></i>Batal</a>
        <button type="submit" class="btn btn-primary px-4"><i data-lucide="check" aria-hidden="true"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Buku' }}</button>
    </div>
</form>

@push('scripts')
<script>
    (function () {
        const preview = document.getElementById('coverPreview');
        const fileInput = document.getElementById('photo');
        const placeholder = '{{ asset('img/book-placeholder.svg') }}';
        const existingPhoto = @json($isEdit ? $book->photo : null);
        const removeFlag = document.getElementById('removePhotoFlag');

        const btnPilih = document.getElementById('btnPilihFoto');
        const btnGanti = document.getElementById('btnGantiFoto');
        const btnHapus = document.getElementById('btnHapusFoto');

        // Klik tombol mana pun akan membuka file picker (jika belum memilih foto baru)
        if (btnPilih) btnPilih.addEventListener('click', () => fileInput.click());
        if (btnGanti) btnGanti.addEventListener('click', () => fileInput.click());

        // Tombol Hapus Foto: reset preview ke placeholder & tandai penghapusan
        if (btnHapus) {
            btnHapus.addEventListener('click', () => {
                preview.src = placeholder;
                if (removeFlag) removeFlag.value = '1';
                fileInput.value = '';
            });
        }

        // Preview segera setelah user memilih file
        fileInput.addEventListener('change', () => {
            const file = fileInput.files[0];
            if (!file) return;
            if (removeFlag) removeFlag.value = '0';
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; };
            reader.readAsDataURL(file);
        });
    })();
</script>
@endpush