<x-guest-layout>
    <x-slot name="title">Halaman tidak ditemukan</x-slot>
    <p class="text-sm font-semibold text-brand tnum">Kesalahan 404</p>
    <h1 class="text-lg font-semibold text-navy mt-1">Halaman tidak ditemukan</h1>
    <p class="text-sm text-muted mt-2">Alamat yang Anda tuju tidak tersedia atau sudah dipindahkan.</p>
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn-primary mt-5">Kembali ke beranda</a>
</x-guest-layout>
