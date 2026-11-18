@extends('layouts.app')

@section('title', 'Laporan & Statistik')

@section('content')
@php
    $maxType = max(1, (int) $recapByType->max('documents_count'));
    $maxClass = max(1, (int) $recapByClass->max());
    $sumStatus = max(1, array_sum($recapByStatus));
    $stageLabels = ['diajukan' => 'Menunggu verifikasi', 'revisi' => 'Perlu revisi', 'menunggu_tte' => 'Menunggu TTE', 'selesai' => 'Selesai', 'ditolak' => 'Ditolak'];
@endphp

<x-page-header title="Laporan & Statistik" subtitle="Rekapitulasi dokumen dan layanan untuk kebutuhan pelaporan sekolah.">
    <x-slot name="actions">
        <form method="GET" class="flex">
            <select name="academic_year" onchange="this.form.submit()" class="input w-full sm:w-52" aria-label="Tahun ajaran">
                <option value="">Semua Tahun Ajaran</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year }}" @selected($academicYear == $year)>{{ $year }}</option>
                @endforeach
            </select>
        </form>
        <a href="{{ route('reports.export', ['academic_year' => $academicYear]) }}" class="btn-secondary"><x-nav-icon name="download" :size="15" /> Unduh CSV</a>
    </x-slot>
</x-page-header>

<div class="stat-strip">
    <x-stat-card label="Total Dokumen" :value="$totalDocuments" :hint="$academicYear ? 'Tahun ajaran '.$academicYear : 'Semua tahun ajaran'" />
    <x-stat-card label="Terverifikasi" :value="$recapByStatus['verified']" />
    <x-stat-card label="Menunggu Verifikasi" :value="$recapByStatus['pending']" tone="text-warning" />
    <x-stat-card label="Ditolak" :value="$recapByStatus['rejected']" />
</div>

<section class="panel p-5 mb-6">
    <h2 class="panel-head -mx-5 -mt-5 mb-4">Komposisi status dokumen</h2>
    @if($totalDocuments > 0)
        <div class="flex h-3 overflow-hidden bg-canvas" role="img" aria-label="Komposisi status dokumen">
            <div class="bg-success" style="width: {{ round($recapByStatus['verified'] / $sumStatus * 100, 1) }}%"></div>
            <div class="bg-warning" style="width: {{ round($recapByStatus['pending'] / $sumStatus * 100, 1) }}%"></div>
            <div class="bg-danger" style="width: {{ round($recapByStatus['rejected'] / $sumStatus * 100, 1) }}%"></div>
        </div>
        <div class="flex flex-wrap gap-x-6 gap-y-2 mt-3 text-xs text-muted">
            <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 bg-success"></span>Terverifikasi {{ round($recapByStatus['verified'] / $sumStatus * 100) }}%</span>
            <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 bg-warning"></span>Menunggu {{ round($recapByStatus['pending'] / $sumStatus * 100) }}%</span>
            <span class="inline-flex items-center gap-2"><span class="w-2.5 h-2.5 bg-danger"></span>Ditolak {{ round($recapByStatus['rejected'] / $sumStatus * 100) }}%</span>
        </div>
    @else
        <p class="text-sm text-muted">Belum ada dokumen pada periode ini.</p>
    @endif
</section>

<div class="grid lg:grid-cols-2 gap-6">
    <section class="panel p-5">
        <h2 class="panel-head -mx-5 -mt-5 mb-4">Rekap per Jenis Dokumen</h2>
        <div class="space-y-3">
            @forelse($recapByType as $type)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-ink">{{ $type->name }}</span>
                        <span class="font-medium text-navy tnum">{{ $type->documents_count }}</span>
                    </div>
                    <div class="h-2 bg-canvas overflow-hidden"><div class="h-full bg-brand" style="width: {{ round($type->documents_count / $maxType * 100) }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-muted">Belum ada jenis dokumen.</p>
            @endforelse
        </div>
    </section>

    <section class="panel p-5">
        <h2 class="panel-head -mx-5 -mt-5 mb-4">Rekap per Kelas</h2>
        <div class="space-y-3">
            @forelse($recapByClass as $class => $total)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-ink">{{ $class ?: '—' }}</span>
                        <span class="font-medium text-navy tnum">{{ $total }}</span>
                    </div>
                    <div class="h-2 bg-canvas overflow-hidden"><div class="h-full bg-institute" style="width: {{ round($total / $maxClass * 100) }}%"></div></div>
                </div>
            @empty
                <p class="text-sm text-muted">Belum ada data.</p>
            @endforelse
        </div>
    </section>

    <section class="panel p-5 lg:col-span-2">
        <h2 class="panel-head -mx-5 -mt-5 mb-4"><span>Layanan permohonan dokumen</span><span class="text-xs font-normal text-muted">Seluruh periode</span></h2>
        <dl class="grid grid-cols-2 md:grid-cols-5 gap-3">
            @foreach($stageLabels as $stage => $label)
                <div class="border border-line rounded-lg p-3">
                    <dt class="text-xs text-muted">{{ $label }}</dt>
                    <dd class="text-2xl font-semibold text-navy tnum mt-0.5">{{ $requestStats[$stage] ?? 0 }}</dd>
                </div>
            @endforeach
        </dl>
    </section>
</div>
@endsection
