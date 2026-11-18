@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $__h = (int) now()->format('H');
    $sapa = $__h < 11 ? 'Selamat pagi' : ($__h < 15 ? 'Selamat siang' : ($__h < 19 ? 'Selamat sore' : 'Selamat malam'));
    $active = $recentRequests->firstWhere(fn ($r) => in_array($r->stage, ['diajukan','revisi','menunggu_tte']));
    $stepIndex = ['diajukan' => 1, 'revisi' => 1, 'menunggu_tte' => 2, 'selesai' => 3];
    $labels = ['Diajukan', 'Verifikasi TU', 'Tanda tangan', 'Selesai'];
@endphp

<x-page-header :title="$student->name" :subtitle="$sapa.'. Kelas '.$student->class.' · NISN '.$student->nisn.' · Status '.ucfirst($student->status)">
    <x-slot name="actions">
        <a href="{{ route('portal.requests.create') }}" class="btn-primary">Ajukan dokumen baru</a>
    </x-slot>
</x-page-header>

@if(\App\Models\Setting::get('announcement'))
    <div class="mb-5 border border-brand/40 bg-softblue px-4 py-3 text-sm text-navy rounded">
        <p class="font-semibold">Pengumuman Tata Usaha</p>
        <p>{{ \App\Models\Setting::get('announcement') }}</p>
    </div>
@endif

@if($needsRevision)
    <div class="mb-5 border border-danger/40 bg-danger/10 px-4 py-3 text-sm text-ink rounded">
        Ada <b>{{ $needsRevision }}</b> pengajuan yang perlu diperbaiki. Catatan dari Tata Usaha sudah tersedia.
        <a href="{{ route('portal.requests.index') }}" class="text-brand font-medium hover:underline">Lihat catatan</a>
    </div>
@endif

<div class="stat-strip">
    <x-stat-card label="Total dokumen" :value="$totalDocuments" :href="route('portal.documents')" />
    <x-stat-card label="Pengajuan aktif" :value="$activeRequests" :href="route('portal.requests.index')" />
    <x-stat-card label="Perlu revisi" :value="$needsRevision" :tone="$needsRevision ? 'text-danger' : 'text-navy'" />
    <x-stat-card label="Selesai" :value="$completedRequests" />
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6 min-w-0">
        <section class="panel">
            <div class="panel-head"><span>Pengajuan yang sedang berjalan</span><a href="{{ route('portal.requests.index') }}" class="text-xs font-normal text-brand hover:underline">Semua riwayat</a></div>
            <div class="p-4">
                @if($active)
                    <p class="text-xs text-muted">{{ $active->ticket_number }}</p>
                    <p class="font-semibold text-navy mb-4">{{ $active->service_name }}</p>
                    @php $cur = $stepIndex[$active->stage]; @endphp
                    <ol class="grid grid-cols-4 gap-px bg-line border border-line rounded overflow-hidden text-center text-xs">
                        @foreach($labels as $i => $label)
                            @php $state = $i < $cur ? 'done' : ($i === $cur ? 'now' : 'todo'); @endphp
                            <li class="p-2.5 {{ $state === 'done' ? 'bg-success/10 text-success' : ($state === 'now' ? ($active->stage === 'revisi' ? 'bg-danger text-white' : 'bg-navy text-white') : 'bg-white text-muted') }}">
                                <span class="block tnum">{{ $i + 1 }}</span>{{ $label }}
                            </li>
                        @endforeach
                    </ol>
                    <p class="mt-4 text-sm {{ $active->stage === 'revisi' ? 'text-danger' : 'text-ink/80' }}">
                        @if($active->stage === 'revisi') Catatan TU: {{ $active->tu_notes }}
                        @elseif($active->stage === 'menunggu_tte') Berkas sudah lolos verifikasi. Tinggal menunggu tanda tangan.
                        @else Berkas sedang diperiksa Tata Usaha. Estimasi selesai: {{ optional($active->sla_due_at)->translatedFormat('d F Y') ?? '1–3 hari kerja' }}. @endif
                    </p>
                @else
                    <x-empty-state title="Tidak ada pengajuan yang berjalan" text="Butuh surat keterangan atau legalisir? Ajukan dari rumah." action="Ajukan dokumen" :href="route('portal.requests.create')" />
                @endif
            </div>
        </section>

        <section class="panel">
            <div class="panel-head"><span>Dokumen terverifikasi</span><a href="{{ route('portal.documents') }}" class="text-xs font-normal text-brand hover:underline">Buka semua</a></div>
            <div class="divide-y divide-line">
                @forelse($quickDownloads as $doc)
                    <div class="flex items-center justify-between gap-3 px-4 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-ink">{{ $doc->documentType->name }}@if($doc->isSigned()) <span class="badge text-success ml-1">Bertanda tangan</span>@endif</p>
                            <p class="text-xs text-muted truncate">{{ $doc->document_number ?: '—' }}</p>
                        </div>
                        <a href="{{ $doc->fileUrl(true) }}" target="_blank" rel="noopener" class="btn-secondary btn-sm shrink-0">Unduh</a>
                    </div>
                @empty
                    <x-empty-state title="Belum ada dokumen terverifikasi" text="Dokumen resmi Anda akan muncul di sini setelah diterbitkan." />
                @endforelse
            </div>
        </section>
    </div>

    <div>
        <section class="panel">
            <div class="panel-head">Catatan</div>
            <ul class="p-4 space-y-2 text-sm text-ink/80 list-disc list-inside">
                <li>Siapkan hasil pindaian berkas yang jelas dan tidak buram.</li>
                <li>Ukuran maksimal tiap berkas 5 MB, format PDF, JPG, atau PNG.</li>
                <li>Salinan fisik diambil di loket TU dengan membawa kartu pelajar.</li>
            </ul>
        </section>
    </div>
</div>
@endsection
