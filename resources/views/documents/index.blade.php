@extends('layouts.app')

@section('title', 'Verifikasi Dokumen')

@section('content')
<x-page-header title="Verifikasi Dokumen" subtitle="Periksa dokumen yang diunggah, lalu verifikasi atau tolak dengan catatan yang jelas.">
    <x-slot name="actions">
        <a href="{{ route('documents.create') }}" class="btn-primary"><x-nav-icon name="upload" :size="16" /> Tambah Dokumen</a>
    </x-slot>
</x-page-header>

<div class="stat-strip">
    <x-stat-card label="Total Dokumen" :value="$counts['total']" hint="Seluruh arsip" />
    <x-stat-card label="Menunggu" :value="$counts['pending']" tone="text-warning" :href="route('documents.index', ['status' => 'pending'])" />
    <x-stat-card label="Terverifikasi" :value="$counts['verified']" :href="route('documents.index', ['status' => 'verified'])" />
    <x-stat-card label="Ditolak" :value="$counts['rejected']" :href="route('documents.index', ['status' => 'rejected'])" />
</div>

<div class="panel">
    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-line">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN" class="input flex-1 min-w-[200px]">
        <select name="document_type_id" class="input w-full sm:w-52">
            <option value="">Semua Jenis Dokumen</option>
            @foreach($documentTypes as $type)
                <option value="{{ $type->id }}" @selected(request('document_type_id') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <select name="status" class="input w-full sm:w-44">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status')=='pending')>Menunggu</option>
            <option value="verified" @selected(request('status')=='verified')>Terverifikasi</option>
            <option value="rejected" @selected(request('status')=='rejected')>Ditolak</option>
        </select>
        <button class="btn-secondary w-full sm:w-auto">Terapkan</button>
        @if(request('search') || request('status') || request('document_type_id'))
            <a href="{{ route('documents.index') }}" class="btn-secondary w-full sm:w-auto">Atur Ulang</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="rtable w-full min-w-[760px] text-sm">
            <thead class="bg-canvas text-muted text-xs border-b border-line">
                <tr>
                    <th class="text-left px-4 py-3">NISN</th>
                    <th class="text-left px-4 py-3">Nama Siswa</th>
                    <th class="text-left px-4 py-3">Jenis Dokumen</th>
                    <th class="text-left px-4 py-3">Tanggal Upload</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($documents as $doc)
                    @php
                        $badge = match($doc->status) {
                            'verified' => ['bg-success/10 text-success', 'Terverifikasi'],
                            'rejected' => ['bg-danger/10 text-danger', 'Ditolak'],
                            default => ['bg-warning/10 text-warning', 'Menunggu'],
                        };
                    @endphp
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3 tnum" data-label="NISN">{{ $doc->student->nisn }}</td>
                        <td class="px-4 py-3 font-medium text-ink" data-label="Nama Siswa">{{ $doc->student->name }}</td>
                        <td class="px-4 py-3" data-label="Jenis Dokumen">{{ $doc->documentType->name }}</td>
                        <td class="px-4 py-3 text-muted" data-label="Tanggal Upload">{{ $doc->created_at->translatedFormat('d M Y') }}</td>
                        <td class="px-4 py-3" data-label="Status"><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                        <td class="px-4 py-3" data-label="Aksi">
                            <div x-data="{ open: false }" class="min-w-0">
                                <div class="flex flex-wrap gap-2 justify-end sm:justify-start">
                                    <a href="{{ $doc->fileUrl() }}" target="_blank" rel="noopener" class="icon-btn" title="Lihat berkas" aria-label="Lihat berkas"><x-nav-icon name="eye" :size="14" /></a>
                                    @if($doc->status === 'pending')
                                        <form action="{{ route('documents.verify', $doc) }}" method="POST">
                                            @csrf
                                            <button class="icon-btn hover:!bg-success/10" title="Verifikasi" aria-label="Verifikasi"><x-nav-icon name="check" :size="14" /></button>
                                        </form>
                                        <button type="button" @click="open = !open" class="icon-btn hover:!bg-danger/10" title="Tolak" aria-label="Tolak"><x-nav-icon name="x" :size="14" /></button>
                                    @endif
                                    <a href="{{ route('documents.edit', $doc) }}" class="icon-btn" title="Ubah" aria-label="Ubah"><x-nav-icon name="edit" :size="14" /></a>
                                    <form action="{{ route('documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn hover:!bg-danger/10" title="Hapus" aria-label="Hapus"><x-nav-icon name="trash" :size="14" /></button>
                                    </form>
                                </div>
                                @if($doc->status === 'pending')
                                    <form x-show="open" x-cloak action="{{ route('documents.reject', $doc) }}" method="POST" class="mt-2 flex gap-2">
                                        @csrf
                                        <input type="text" name="notes" required placeholder="Alasan penolakan" class="input !text-xs flex-1 min-w-0">
                                        <button class="btn-danger btn-sm">Kirim</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6"><x-empty-state title="Dokumen tidak ditemukan" text="Coba ubah kata pencarian atau filter yang dipakai." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-line">{{ $documents->links() }}</div>
</div>
@endsection
