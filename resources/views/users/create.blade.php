@extends('layouts.app')
@section('title', 'Tambah Pengguna')
@section('content')
<x-page-header title="Tambah Akun Pengguna" subtitle="Buat akun baru untuk administrator, petugas Tata Usaha, atau siswa." />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('users.store') }}" method="POST">@include('users._form')</form>
    </div>
    <aside class="panel p-5 self-start">
        <h2 class="font-heading text-base font-semibold text-navy mb-3">Hak Akses Peran</h2>
        <dl class="space-y-3 text-sm leading-relaxed">
            <div><dt class="font-medium text-ink">Administrator</dt><dd class="text-muted">Seluruh fitur, termasuk pengguna dan pengaturan sekolah.</dd></div>
            <div><dt class="font-medium text-ink">Petugas TU</dt><dd class="text-muted">Verifikasi permohonan, dokumen, data siswa, dan laporan.</dd></div>
            <div><dt class="font-medium text-ink">Siswa</dt><dd class="text-muted">Portal siswa. Wajib dihubungkan ke data siswa.</dd></div>
        </dl>
    </aside>
</div>
@endsection
