@extends('layouts.app')
@section('title', 'Ubah Pengguna')
@section('content')
<x-page-header title="Ubah Akun Pengguna" :subtitle="$user->name.' · '.$user->email" />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('users.update', $user) }}" method="POST">@method('PUT')@include('users._form')</form>
    </div>
    <aside class="panel p-5 self-start">
        <h2 class="font-heading text-base font-semibold text-navy mb-3">Aktivitas Akun</h2>
        <dl class="divide-y divide-line text-sm">
            <div class="py-2"><dt class="text-xs text-muted">Masuk terakhir</dt><dd class="font-medium text-ink">{{ $user->last_login_at ? $user->last_login_at->translatedFormat('d M Y, H:i') : 'Belum ada catatan' }}</dd></div>
            <div class="py-2"><dt class="text-xs text-muted">Akun dibuat</dt><dd class="font-medium text-ink">{{ $user->created_at->translatedFormat('d M Y') }}</dd></div>
        </dl>
        @if($user->id === auth()->id())
            <p class="text-xs text-muted mt-3 leading-relaxed">Ini akun Anda sendiri. Peran tidak dapat diturunkan dari halaman ini.</p>
        @endif
    </aside>
</div>
@endsection
