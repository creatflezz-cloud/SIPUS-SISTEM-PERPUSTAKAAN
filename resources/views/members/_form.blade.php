@php $isEdit = isset($member) && $member !== null; @endphp

<form method="POST" action="{{ $isEdit ? route('members.update', $member) : route('members.store') }}" class="row g-3">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <div class="col-md-6">
        <label for="member_code" class="form-label">Nomor Anggota <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('member_code') is-invalid @enderror"
               id="member_code" name="member_code" value="{{ old('member_code', $member->member_code ?? '') }}" required placeholder="cth: ANG-0001">
        @error('member_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
        <input type="text" class="form-control @error('name') is-invalid @enderror"
               id="name" name="name" value="{{ old('name', $member->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label for="gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
        <select name="gender" id="gender" class="form-select">
            <option value="L" @selected(old('gender', $member->gender ?? 'L') === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $member->gender ?? '') === 'P')>Perempuan</option>
        </select>
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Nomor Telepon</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $member->phone ?? '') }}" placeholder="cth: 08xxxxxxxxxx">
    </div>
    <div class="col-md-6">
        <label for="status" class="form-label">Status Keanggotaan <span class="text-danger">*</span></label>
        <select name="status" id="status" class="form-select">
            <option value="aktif" @selected(old('status', $member->status ?? 'aktif') === 'aktif')>Aktif</option>
            <option value="tidak_aktif" @selected(old('status', $member->status ?? '') === 'tidak_aktif')>Tidak Aktif</option>
        </select>
    </div>
    <div class="col-12">
        <label for="address" class="form-label">Alamat</label>
        <textarea name="address" id="address" rows="3" class="form-control">{{ old('address', $member->address ?? '') }}</textarea>
    </div>
    <div class="col-12 d-flex justify-content-end gap-2 flex-wrap">
        <a href="{{ route('members.index') }}" class="btn btn-light"><i data-lucide="x" aria-hidden="true"></i>Batal</a>
        <button type="submit" class="btn btn-primary px-4"><i data-lucide="check" aria-hidden="true"></i>{{ $isEdit ? 'Simpan Perubahan' : 'Simpan Anggota' }}</button>
    </div>
</form>