@extends('layouts.app')

@section('title', 'Tambah Dokumen')

@section('content')
<x-page-header title="Unggah Dokumen Akademik" subtitle="Dokumen yang diunggah akan masuk antrean verifikasi Tata Usaha." />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @include('documents._form')
        </form>
    </div>
    <aside class="space-y-4">
        <div class="panel p-5">
            <h2 class="font-heading text-base font-semibold text-navy mb-3">Sebelum Mengunggah</h2>
            <ul class="space-y-2 text-sm text-muted leading-relaxed">
                <li>Pastikan NISN sudah terdaftar di Data Siswa.</li>
                <li>Format berkas PDF, JPG, atau PNG dengan ukuran maksimal 2 MB.</li>
                <li>Isi nomor dokumen dan tahun ajaran agar mudah dicari dan masuk laporan.</li>
            </ul>
        </div>
        <div class="rounded-xl bg-softblue p-5 text-sm text-navy/80 leading-relaxed">
            Status awal dokumen adalah <span class="font-semibold text-navy">Menunggu</span>. Ubah menjadi Terverifikasi setelah berkas diperiksa.
        </div>
    </aside>
</div>
@endsection
