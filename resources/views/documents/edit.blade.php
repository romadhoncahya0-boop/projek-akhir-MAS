@extends('layouts.app')

@section('title', 'Edit Dokumen')

@section('content')
<x-page-header title="Edit Dokumen Akademik" :subtitle="$document->student->name.' · '.$document->documentType->name" />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('documents.update', $document) }}" method="POST" enctype="multipart/form-data">
            @include('documents._form')
        </form>
    </div>
    <aside class="space-y-4">
        <div class="panel p-5">
            <h2 class="font-heading text-base font-semibold text-navy mb-3">Informasi Dokumen</h2>
            @php
                $badge = match($document->status) {
                    'verified' => ['bg-success/10 text-success', 'Terverifikasi'],
                    'rejected' => ['bg-danger/10 text-danger', 'Ditolak'],
                    default => ['bg-warning/10 text-warning', 'Menunggu'],
                };
            @endphp
            <dl class="divide-y divide-line text-sm">
                <div class="flex justify-between items-center py-2"><dt class="text-muted">Status</dt><dd><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></dd></div>
                <div class="flex justify-between py-2"><dt class="text-muted">NISN</dt><dd class="font-medium text-navy tnum">{{ $document->student->nisn }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-muted">Diunggah</dt><dd class="font-medium text-navy">{{ $document->created_at->translatedFormat('d M Y') }}</dd></div>
            </dl>
            @if($document->fileUrl())
                <a href="{{ $document->fileUrl() }}" target="_blank" rel="noopener" class="btn-secondary w-full mt-4"><x-nav-icon name="eye" :size="15" /> Lihat berkas saat ini</a>
            @endif
        </div>
        @if($document->status === 'rejected' && $document->notes)
            <div class="rounded-xl border border-danger/25 bg-danger/5 p-5 text-sm text-danger leading-relaxed">
                <p class="font-semibold mb-1">Alasan penolakan</p>
                {{ $document->notes }}
            </div>
        @endif
    </aside>
</div>
@endsection
