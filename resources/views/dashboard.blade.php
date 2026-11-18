@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $__h = (int) now()->format('H');
    $sapa = $__h < 11 ? 'Selamat pagi' : ($__h < 15 ? 'Selamat siang' : ($__h < 19 ? 'Selamat sore' : 'Selamat malam'));
    $firstName = explode(' ', trim(auth()->user()->name))[0];
    $max = max(1, $monthlyUploads->max() ?? 1);
    $todo = $requestStats['diajukan'] + $requestStats['menunggu_tte'];
@endphp

<x-page-header title="Dashboard" :subtitle="$sapa.', '.$firstName.'. '.($todo > 0 ? 'Ada '.$todo.' permohonan yang perlu diproses.' : 'Tidak ada permohonan yang menunggu.')">
    <x-slot name="actions">
        <a href="{{ route('permohonan.index') }}" class="btn-primary">Buka antrean permohonan</a>
    </x-slot>
</x-page-header>

<div class="stat-strip">
    <x-stat-card label="Siswa aktif" :value="$totalStudents" :href="route('students.index')" />
    <x-stat-card label="Menunggu verifikasi" :value="$requestStats['diajukan']" :href="route('permohonan.index', ['stage'=>'diajukan'])" />
    <x-stat-card label="Menunggu tanda tangan" :value="$requestStats['menunggu_tte']" :href="route('permohonan.index', ['stage'=>'menunggu_tte'])" />
    <x-stat-card label="Permohonan selesai" :value="$requestStats['selesai']" :href="route('reports.index')" />
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6 min-w-0">
        <section class="panel">
            <div class="panel-head"><span>Antrean terlama</span><a href="{{ route('permohonan.index') }}" class="text-xs font-normal text-brand hover:underline">Lihat semua</a></div>
            <div class="divide-y divide-line">
                @forelse($queue as $q)
                    <a href="{{ route('permohonan.index', ['selected'=>$q->id]) }}" class="flex items-center gap-4 px-4 py-3 hover:bg-canvas">
                        <span class="flex-1 min-w-0">
                            <span class="block text-sm font-medium text-ink truncate">{{ $q->student->name }}</span>
                            <span class="block text-xs text-muted truncate">{{ $q->service_name }}</span>
                        </span>
                        <span class="text-xs text-muted shrink-0">{{ $q->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <x-empty-state title="Antrean kosong" text="Belum ada permohonan yang perlu diproses." />
                @endforelse
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">Unggahan dokumen, 6 bulan terakhir</div>
            <div class="p-4">
                @if($monthlyUploads->isEmpty())
                    <x-empty-state title="Belum ada data unggahan" />
                @else
                    <div class="flex items-end gap-3 h-40 border-b border-line">
                        @foreach($monthlyUploads as $month => $total)
                            <div class="flex-1 h-full flex flex-col justify-end items-center gap-1" title="{{ $total }} dokumen">
                                <span class="text-xs text-ink tnum">{{ $total }}</span>
                                <div class="w-full max-w-[48px] bg-institute" style="height: {{ max(4, round($total / $max * 80)) }}%"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex gap-3 pt-1">
                        @foreach($monthlyUploads as $month => $total)
                            <span class="flex-1 text-center text-xs text-muted">{{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('M') }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    </div>

    <div class="space-y-6 min-w-0">
        <section class="panel">
            <div class="panel-head">Pintasan</div>
            <ul class="divide-y divide-line text-sm">
                @foreach([['Tambah siswa', route('students.create')],['Unggah dokumen', route('documents.create')],['Dokumen menunggu verifikasi', route('documents.index', ['status'=>'pending'])],['Laporan dan statistik', route('reports.index')]] as [$l,$h])
                    <li><a href="{{ $h }}" class="flex justify-between px-4 py-2.5 hover:bg-canvas text-ink">{{ $l }}<span class="text-muted">&rsaquo;</span></a></li>
                @endforeach
            </ul>
        </section>

        <section class="panel">
            <div class="panel-head">Dokumen terbaru</div>
            <div class="divide-y divide-line">
                @forelse($recentActivities as $a)
                    @php $b = match($a->status) { 'verified' => ['text-success','Terverifikasi'], 'rejected' => ['text-danger','Ditolak'], default => ['text-warning','Menunggu'] }; @endphp
                    <div class="flex items-start justify-between gap-3 px-4 py-2.5 text-sm">
                        <span class="text-ink min-w-0">{{ $a->student->name }}<span class="block text-xs text-muted truncate">{{ $a->documentType->name }}</span></span>
                        <span class="badge {{ $b[0] }}">{{ $b[1] }}</span>
                    </div>
                @empty
                    <p class="px-4 py-3 text-sm text-muted">Belum ada aktivitas.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
