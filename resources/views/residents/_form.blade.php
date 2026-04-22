@php
    $residentModel = $resident ?? null;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label" for="nik">NIK</label>
        <input type="text" class="form-control" id="nik" name="nik" value="{{ old('nik', $residentModel?->nik) }}" maxlength="16" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="name">Nama</label>
        <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $residentModel?->name) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="gender">Jenis Kelamin</label>
        <select class="form-select" id="gender" name="gender" required>
            <option value="L" @selected(old('gender', $residentModel?->gender) === 'L')>Laki-laki</option>
            <option value="P" @selected(old('gender', $residentModel?->gender) === 'P')>Perempuan</option>
        </select>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="birth_place">Tempat Lahir</label>
        <input type="text" class="form-control" id="birth_place" name="birth_place" value="{{ old('birth_place', $residentModel?->birth_place) }}">
    </div>
    <div class="col-md-4">
        <label class="form-label" for="birth_date">Tanggal Lahir</label>
        <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date', $residentModel?->birth_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-12">
        <label class="form-label" for="address">Alamat</label>
        <textarea class="form-control" id="address" name="address" rows="3" required>{{ old('address', $residentModel?->address) }}</textarea>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="rt">RT</label>
        <input type="text" class="form-control" id="rt" name="rt" value="{{ old('rt', $residentModel?->rt) }}" maxlength="3" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="rw">RW</label>
        <input type="text" class="form-control" id="rw" name="rw" value="{{ old('rw', $residentModel?->rw) }}" maxlength="3" required>
    </div>
    <div class="col-md-3">
        <label class="form-label" for="phone">Nomor HP</label>
        <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $residentModel?->phone) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="occupation">Pekerjaan</label>
        <input type="text" class="form-control" id="occupation" name="occupation" value="{{ old('occupation', $residentModel?->occupation) }}">
    </div>

    <div class="col-12">
        <hr class="my-1">
        <h2 class="h6 mb-0">Akun Login Warga</h2>
        <small class="text-muted">Akun ini digunakan warga untuk login dan mengajukan surat.</small>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="email">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $residentModel?->user?->email) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password">{{ $residentModel ? 'Password Baru (opsional)' : 'Password' }}</label>
        <input type="password" class="form-control" id="password" name="password" @if (! $residentModel) required @endif>
    </div>
    <div class="col-md-6">
        <label class="form-label" for="password_confirmation">Konfirmasi Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" @if (! $residentModel) required @endif>
    </div>
</div>
