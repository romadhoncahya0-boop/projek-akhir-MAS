@extends('layouts.app')

@section('title', 'Tambah Data Siswa')

@section('content')
<x-page-header title="Tambah Data Siswa" subtitle="Isi data induk siswa baru. Akun login Portal Akademik dapat dibuat sekaligus." />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('students.store') }}" method="POST">
            @include('students._form')
        </form>
    </div>
    <aside class="space-y-4">
        <div class="panel p-5">
            <h2 class="font-heading text-base font-semibold text-navy mb-3">Panduan Pengisian</h2>
            <ul class="space-y-2 text-sm text-muted leading-relaxed">
                <li>NISN harus unik dan maksimal 15 karakter.</li>
                <li>Nama ditulis sesuai data sekolah, karena dipakai untuk memeriksa permintaan atur ulang kata sandi.</li>
                <li>Status <span class="font-medium text-ink">Aktif</span> dihitung pada statistik beranda.</li>
            </ul>
        </div>
        <div class="rounded-xl bg-softblue p-5 text-sm text-navy/80 leading-relaxed">
            <p class="font-semibold text-navy mb-1">Akun siswa</p>
            Centang opsi akun login agar siswa dapat masuk, mengajukan dokumen, dan mengunduh arsipnya sendiri.
        </div>
    </aside>
</div>
@endsection
