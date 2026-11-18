<x-guest-layout>
    <x-slot name="title">Terlalu banyak permintaan</x-slot>
    <p class="text-sm font-semibold text-brand tnum">Kesalahan 429</p>
    <h1 class="text-lg font-semibold text-navy mt-1">Terlalu banyak permintaan</h1>
    <p class="text-sm text-muted mt-2">Mohon tunggu beberapa saat sebelum mencoba lagi.</p>
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn-primary mt-5">Kembali ke beranda</a>
</x-guest-layout>
