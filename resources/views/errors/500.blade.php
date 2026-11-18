<x-guest-layout>
    <x-slot name="title">Terjadi kesalahan pada server</x-slot>
    <p class="text-sm font-semibold text-brand tnum">Kesalahan 500</p>
    <h1 class="text-lg font-semibold text-navy mt-1">Terjadi kesalahan pada server</h1>
    <p class="text-sm text-muted mt-2">Maaf, terjadi gangguan. Silakan coba lagi beberapa saat lagi.</p>
    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="btn-primary mt-5">Kembali ke beranda</a>
</x-guest-layout>
