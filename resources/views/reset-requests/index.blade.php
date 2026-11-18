@extends('layouts.app')
@section('title', 'Permintaan Kata Sandi')
@section('content')
<x-page-header title="Permintaan Kata Sandi" subtitle="Siswa yang lupa kata sandi muncul di sini. Pastikan identitasnya (kartu pelajar) sebelum memberikan kata sandi sementara." />

@if(session('temp_password'))
    <div class="mb-6 rounded border border-brand/40 bg-softblue p-4" x-data="{c:false}">
        <p class="text-sm text-navy">Kata sandi sementara untuk <b>{{ session('temp_password')['name'] }}</b>:</p>
        <div class="flex flex-wrap items-center gap-3 mt-2">
            <code class="text-xl font-semibold tracking-widest text-navy bg-white border border-line rounded px-3 py-1.5 tnum" id="tp">{{ session('temp_password')['password'] }}</code>
            <button type="button" class="btn-secondary" @click="navigator.clipboard.writeText(document.getElementById('tp').innerText); c=true; setTimeout(()=>c=false,2000)">
                <x-nav-icon name="copy" :size="14" /><span x-text="c ? 'Tersalin' : 'Salin'"></span>
            </button>
        </div>
        <p class="text-xs text-navy/80 mt-3">Kata sandi ini hanya tampil sekali. Berikan langsung kepada siswa yang bersangkutan. Siswa akan diminta membuat kata sandi baru saat masuk.</p>
    </div>
@endif

<div class="panel">
    <div class="flex flex-wrap gap-2 p-4 border-b border-line text-sm">
        @foreach(['pending' => 'Menunggu', 'done' => 'Selesai', 'dismissed' => 'Ditutup', 'all' => 'Semua'] as $k => $l)
            <a href="{{ route('reset-requests.index', ['status' => $k]) }}" class="px-3 py-1 rounded {{ $status === $k ? 'bg-navy text-white' : 'bg-canvas text-ink hover:bg-line' }}">{{ $l }}</a>
        @endforeach
    </div>
    <div class="divide-y divide-line">
        @forelse($requests as $r)
            <div class="p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-ink">{{ $r->student->name }} <span class="text-xs text-muted tnum">NISN {{ $r->student->nisn }} &middot; {{ $r->student->class }}</span></p>
                    <p class="text-xs text-muted">Diminta {{ $r->created_at->diffForHumans() }}@if($r->note) &middot; Catatan: {{ $r->note }}@endif</p>
                    @if($r->status !== 'pending')
                        <p class="text-xs text-muted mt-1">{{ $r->status === 'done' ? 'Diselesaikan' : 'Ditutup' }} oleh {{ $r->handler?->name ?? '-' }} &middot; {{ $r->handled_at?->translatedFormat('d M Y H:i') }}</p>
                    @endif
                </div>
                @if($r->status === 'pending')
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('reset-requests.reset', $r) }}" onsubmit="return confirm('Buat kata sandi sementara untuk {{ $r->student->name }}? Kata sandi lamanya akan diganti.')">@csrf
                            <button class="btn-primary">Buat kata sandi sementara</button>
                        </form>
                        <form method="POST" action="{{ route('reset-requests.dismiss', $r) }}" onsubmit="return confirm('Tutup permintaan ini tanpa mengubah kata sandi?')">@csrf
                            <button class="btn-secondary">Tutup</button>
                        </form>
                    </div>
                @else
                    <span class="badge {{ $r->status === 'done' ? 'text-success' : 'text-muted' }}">{{ $r->status === 'done' ? 'Selesai' : 'Ditutup' }}</span>
                @endif
            </div>
        @empty
            <x-empty-state title="Belum ada permintaan" text="Permintaan atur ulang kata sandi dari siswa akan muncul di sini." />
        @endforelse
    </div>
    <div class="p-4 border-t border-line">{{ $requests->links() }}</div>
</div>
@endsection
