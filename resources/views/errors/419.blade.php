<x-guest-layout>
    <x-slot name="title">Sesi telah berakhir</x-slot>
    <p class="text-sm font-semibold text-brand tnum">Kesalahan 419</p>
    <h1 class="text-lg font-semibold text-navy mt-1">Sesi telah berakhir</h1>
    <p class="text-sm text-muted mt-2">Halaman sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.</p>
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn-primary mt-5">Kembali ke beranda</a>
</x-guest-layout>
