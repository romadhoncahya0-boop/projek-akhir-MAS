<x-guest-layout>
    <x-slot name="title">Akses ditolak</x-slot>
    <p class="text-sm font-semibold text-brand tnum">Kesalahan 403</p>
    <h1 class="text-lg font-semibold text-navy mt-1">Akses ditolak</h1>
    <p class="text-sm text-muted mt-2">Anda tidak memiliki izin untuk membuka halaman ini.</p>
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn-primary mt-5">Kembali ke beranda</a>
</x-guest-layout>
