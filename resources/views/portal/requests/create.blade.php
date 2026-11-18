@extends('layouts.app')

@section('title', 'Ajukan Dokumen')

@section('content')
<x-page-header title="Ajukan Dokumen" subtitle="Lengkapi formulir permohonan dan unggah berkas pendukung. Tata Usaha akan memeriksanya." />

<div class="grid lg:grid-cols-3 gap-6">
    <form method="POST" action="{{ route('portal.requests.store') }}" enctype="multipart/form-data" class="lg:col-span-2 panel p-5 md:p-6 space-y-5">
        @csrf

        <div class="flex items-center gap-3 pb-4 border-b border-line">
            <div class="w-10 h-10 rounded bg-softblue flex items-center justify-center text-brand"><x-nav-icon name="file" :size="20" class="" /></div>
            <div>
                <p class="text-xs font-semibold text-brand">Dokumen Resmi</p>
                <p class="text-sm text-muted">Pilih jenis dokumen dan lengkapi detail permohonan Anda.</p>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Jenis Dokumen *</label>
            <select name="document_type_id" required class="input">
                <option value="">— Pilih Jenis Dokumen —</option>
                @foreach($documentTypes as $type)
                    <option value="{{ $type->id }}" @selected(old('document_type_id') == $type->id)>{{ $type->name }} ({{ $type->category }})</option>
                @endforeach
            </select>
            @error('document_type_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Nama Layanan / Judul Permohonan *</label>
            <input type="text" name="service_name" value="{{ old('service_name') }}" required
                   placeholder="Contoh: Surat Keterangan Rekomendasi SNBP & Legalisir Rapor"
                   class="input">
            @error('service_name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Tujuan Pengajuan Dokumen *</label>
            <input type="text" name="purpose" value="{{ old('purpose') }}" required
                   placeholder="Contoh: Pendaftaran Seleksi Nasional Berdasarkan Prestasi (SNBP) 2025"
                   class="input">
            @error('purpose')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-2">Jumlah Salinan &amp; Format Distribusi Dokumen *</label>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="border border-line rounded p-4">
                    <p class="text-sm font-medium text-ink mb-2">Salinan Fisik (Cetak)</p>
                    <p class="text-xs text-muted mb-3">Legalisir dengan cap stempel TU</p>
                    <input type="number" name="physical_copies" value="{{ old('physical_copies', 1) }}" min="0" max="5"
                           class="w-24 rounded border-line text-sm">
                    <p class="text-[11px] text-muted mt-1">Maks. 5 rangkap, diambil di Loket TU</p>
                </div>
                <div class="border border-line rounded p-4">
                    <label class="flex items-center justify-between cursor-pointer">
                        <span>
                            <span class="text-sm font-medium text-ink block">E-Dokumen Digital</span>
                            <span class="text-xs text-muted">Berkas PDF dapat diunduh dari portal</span>
                        </span>
                        <input type="checkbox" name="digital_copy" value="1" @checked(old('digital_copy', true)) class="rounded border-line text-brand">
                    </label>
                    <p class="text-xs text-muted mt-3">Siap diunduh setelah selesai</p>
                </div>
            </div>
            @error('physical_copies')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Keperluan Khusus / Keterangan Tambahan</label>
            <textarea name="notes" rows="3" maxlength="250"
                      placeholder="Tercantum jika pihak perguruan tinggi memerlukan peruntukan surat secara spesifik."
                      class="input">{{ old('notes') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Berkas Pendukung Utama * <span class="text-xs text-muted font-normal">(PDF, JPG, atau PNG, maks 5MB)</span></label>
            <input type="file" name="support_file" required accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full text-sm rounded border-line file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-softblue file:text-brand">
            @error('support_file')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Berkas Pendukung Tambahan <span class="text-xs text-muted font-normal">(opsional — surat pengantar wali kelas / bukti bebas pustaka)</span></label>
            <input type="file" name="additional_file" accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full text-sm rounded border-line file:mr-3 file:py-2 file:px-3 file:rounded file:border-0 file:bg-canvas file:text-muted">
        </div>

        <label class="flex items-start gap-3 bg-canvas rounded p-4 text-xs text-muted">
            <input type="checkbox" name="agree" value="1" required class="mt-0.5 rounded border-line text-brand">
            Saya menyatakan bahwa seluruh informasi dan dokumen yang dilampirkan adalah benar, sah, dan sesuai dengan rekam jejak akademik saya di Dapodik {{ config('app.name') }}. Penyalahgunaan data merupakan pelanggaran etik akademik.
        </label>
        @error('agree')<p class="text-xs text-danger">{{ $message }}</p>@enderror

        <div class="flex flex-col-reverse sm:flex-row sm:justify-between gap-3 pt-2">
            <a href="{{ route('portal.requests.index') }}" class="btn-secondary">← Kembali</a>
            <button type="submit" class="btn-primary">Ajukan Permohonan →</button>
        </div>
    </form>

    <aside class="space-y-4 self-start">
        <div class="panel p-5">
            <h3 class="font-semibold text-ink mb-3">Persyaratan &amp; Prosedur</h3>
            <ul class="space-y-2 text-xs text-muted">
                <li>Telah melunasi administrasi komite sekolah semester berjalan.</li>
                <li>Bebas pinjaman buku literatur dari Perpustakaan.</li>
                <li>Masa berlaku legalisir resmi adalah 6 bulan sejak tanggal penerbitan tanda tangan.</li>
                <li>Siswa wajib membawa kartu identitas asli saat pengambilan salinan fisik.</li>
            </ul>
        </div>
        <div class="panel p-5">
            <h3 class="font-semibold text-ink mb-3">Alur &amp; Estimasi Waktu</h3>
            <ol class="space-y-3 text-xs">
                <li><span class="font-semibold text-navy">1. Pengajuan Masuk</span><br><span class="text-muted">Permohonan terdaftar di sistem antrean loket Tata Usaha.</span></li>
                <li><span class="font-semibold text-navy">2. Verifikasi Berkas TU</span><br><span class="text-muted">Pemeriksaan validitas rekam nilai &amp; stempel buku induk.</span></li>
                <li><span class="font-semibold text-navy">3. Tanda tangan</span><br><span class="text-muted">Penandatanganan oleh Kepala Sekolah.</span></li>
                <li><span class="font-semibold text-navy">4. Pengambilan/Unduh</span><br><span class="text-muted">Dokumen siap diambil di TU atau diunduh via portal.</span></li>
            </ol>
        </div>
    </aside>
</div>
@endsection
