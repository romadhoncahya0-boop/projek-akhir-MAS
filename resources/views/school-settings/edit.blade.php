@extends('layouts.app')
@section('title', 'Pengaturan Sekolah')
@section('content')
<x-page-header title="Pengaturan Sekolah" subtitle="Informasi ini tampil di beranda, footer, dan dashboard siswa, serta menentukan batas waktu layanan." />
<form method="POST" action="{{ route('school-settings.update') }}" class="panel p-5 md:p-6 max-w-3xl space-y-8">
    @csrf @method('PUT')
    @php $f = fn($k) => old($k, $values[$k]); @endphp

    <div>
        <h2 class="section-title border-b border-line pb-2 mb-4">Identitas &amp; Kontak</h2>
        <div class="grid sm:grid-cols-2 gap-5">
            <div><label class="label">Nama sekolah</label>
                <input type="text" name="school_name" value="{{ $f('school_name') }}" required class="input">@error('school_name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Email Tata Usaha</label>
                <input type="email" name="contact_email" value="{{ $f('contact_email') }}" required class="input">@error('contact_email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Telepon / WhatsApp (opsional)</label>
                <input type="text" name="contact_phone" value="{{ $f('contact_phone') }}" class="input">@error('contact_phone')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Alamat loket</label>
                <input type="text" name="school_address" value="{{ $f('school_address') }}" required class="input">@error('school_address')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
        </div>
    </div>

    <div>
        <h2 class="section-title border-b border-line pb-2 mb-4">Jam &amp; Waktu Layanan</h2>
        <div class="grid sm:grid-cols-3 gap-5">
            <div><label class="label">Hari layanan</label>
                <input type="text" name="service_days" value="{{ $f('service_days') }}" required class="input">@error('service_days')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Jam layanan</label>
                <input type="text" name="service_hours" value="{{ $f('service_hours') }}" required class="input">@error('service_hours')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror</div>
            <div><label class="label">Estimasi proses (hari kerja)</label>
                <input type="number" name="sla_days" value="{{ $f('sla_days') }}" min="1" max="14" required class="input">@error('sla_days')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                <p class="text-xs text-muted mt-1">Dipakai sebagai batas waktu (SLA) pengajuan baru.</p></div>
        </div>
    </div>

    <div>
        <h2 class="section-title border-b border-line pb-2 mb-2">Pengumuman untuk Siswa</h2>
        <p class="text-sm text-muted mb-3">Tampil di dashboard siswa. Kosongkan untuk menyembunyikan.</p>
        <textarea name="announcement" rows="3" maxlength="500" class="input">{{ $f('announcement') }}</textarea>
        @error('announcement')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="flex gap-3"><button class="btn-primary">Simpan pengaturan</button><a href="{{ route('dashboard') }}" class="btn-secondary">Batal</a></div>
</form>
@endsection
