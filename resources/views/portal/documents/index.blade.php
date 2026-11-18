@extends('layouts.app')

@section('title', 'Dokumen Saya')

@section('content')
<x-page-header title="Dokumen Saya" subtitle="Arsip dokumen akademik Anda yang sudah diverifikasi Tata Usaha. Unduh kapan saja saat dibutuhkan." />

<div class="stat-strip !grid-cols-2 lg:!grid-cols-2 max-w-md">
    <x-stat-card label="Total berkas" :value="$totalDocuments" />
    <x-stat-card label="Kategori" :value="count($categories)" />
</div>

<div class="panel">
    <div class="flex gap-1 p-3 border-b border-line overflow-x-auto">
        <a href="{{ route('portal.documents') }}"
           class="text-sm px-4 py-2 whitespace-nowrap {{ !$activeCategory ? 'bg-navy text-white' : 'text-ink hover:bg-canvas' }}">
            Semua Dokumen <span class="text-xs text-muted">{{ $totalDocuments }}</span>
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('portal.documents', ['category' => $cat]) }}"
               class="text-sm px-4 py-2 whitespace-nowrap {{ $activeCategory === $cat ? 'bg-navy text-white' : 'text-ink hover:bg-canvas' }}">
                {{ $cat }} <span class="text-xs text-muted">{{ $counts[$cat] }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-line">
        @if($activeCategory) <input type="hidden" name="category" value="{{ $activeCategory }}"> @endif
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama berkas atau kata kunci..."
               class="input flex-1 min-w-[200px]">
        <button class="btn-secondary w-full sm:w-auto">Terapkan</button>
    </form>

    <div class="overflow-x-auto">
    <table class="rtable w-full min-w-[720px] text-sm">
        <thead class="bg-canvas text-muted text-xs border-b border-line">
            <tr>
                <th class="text-left px-4 py-3">Nama Dokumen &amp; ID</th>
                <th class="text-left px-4 py-3">Kategori</th>
                <th class="text-left px-4 py-3">Tahun Ajaran</th>
                <th class="text-left px-4 py-3">Tanggal Terbit</th>
                <th class="text-left px-4 py-3">Status</th>
                <th class="text-left px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse($documents as $doc)
                <tr class="hover:bg-canvas">
                    <td class="px-4 py-3" data-label="Dokumen">
                        <div class="min-w-0">
                            <p class="font-medium text-ink">{{ $doc->documentType->name }}</p>
                            <p class="text-xs text-muted break-all">ID: {{ $doc->document_number ?: '—' }}</p>
                        </div>
                    </td>
                    <td class="px-4 py-3" data-label="Kategori"><span class="badge text-muted">{{ $doc->documentType->category }}</span></td>
                    <td class="px-4 py-3 text-muted" data-label="Tahun Ajaran">{{ $doc->academic_year ?: '—' }}</td>
                    <td class="px-4 py-3 text-muted" data-label="Tanggal Terbit">{{ optional($doc->issue_date)->translatedFormat('d M Y') ?? $doc->created_at->translatedFormat('d M Y') }}</td>
                    <td class="px-4 py-3" data-label="Status">
                        @if($doc->isSigned())
                            <span class="badge text-success">Bertanda tangan</span>
                        @elseif($doc->status === 'verified')
                            <span class="badge text-success">Terverifikasi</span>
                        @elseif($doc->status === 'rejected')
                            <span class="badge text-danger">Ditolak</span>
                        @else
                            <span class="badge text-warning">Menunggu</span>
                        @endif
                    </td>
                    <td class="px-4 py-3" data-label="Aksi">
                        <a href="{{ $doc->fileUrl(true) }}" target="_blank" rel="noopener" class="btn-secondary btn-sm"><x-nav-icon name="download" :size="13" /> Unduh</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6"><x-empty-state title="Belum ada dokumen" text="Dokumen pada kategori atau pencarian ini belum tersedia." /></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="p-4 border-t border-line">{{ $documents->links() }}</div>
</div>
@endsection
