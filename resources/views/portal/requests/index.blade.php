@extends('layouts.app')

@section('title', 'Riwayat Pengajuan')

@section('content')
<x-page-header title="Riwayat Pengajuan Dokumen" subtitle="Pantau status setiap pengajuan, baca catatan Tata Usaha, dan unduh dokumen yang sudah terbit.">
    <x-slot name="actions">
        <a href="{{ route('portal.requests.create') }}" class="btn-primary"><x-nav-icon name="file-plus" :size="16" /> Ajukan Dokumen Baru</a>
    </x-slot>
</x-page-header>

<div class="panel divide-y divide-line">
    @forelse($requests as $req)
        @php
            $badge = match($req->stage) {
                'selesai' => ['bg-success/10 text-success', 'Selesai'],
                'revisi' => ['bg-danger/10 text-danger', 'Perlu Revisi'],
                'menunggu_tte' => ['bg-warning/10 text-warning', 'Menunggu TTE'],
                'ditolak' => ['bg-danger/10 text-danger', 'Ditolak'],
                default => ['bg-softblue text-brand', 'Verifikasi TU'],
            };
        @endphp
        <div class="p-4 md:p-5 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0 flex-1 basis-64">
                <p class="text-brand text-sm font-semibold">{{ $req->ticket_number }}</p>
                <p class="font-medium text-ink text-sm mt-0.5">{{ $req->service_name }}</p>
                <p class="text-xs text-muted mt-1">{{ $req->documentType->name }} • Diajukan {{ $req->created_at->translatedFormat('d M Y, H:i') }} WIB</p>
                @if($req->stage === 'revisi' && $req->tu_notes)
                    <p class="text-xs text-danger mt-2 bg-danger/5 rounded px-2 py-1.5">Catatan TU: {{ $req->tu_notes }}</p>
                @endif
                @if($req->stage === 'ditolak' && $req->tu_notes)
                    <p class="text-xs text-danger mt-2 bg-danger/5 rounded px-2 py-1.5">Alasan ditolak: {{ $req->tu_notes }}</p>
                @endif
            </div>
            <div class="sm:text-right shrink-0 max-w-full">
                <span class="badge font-medium px-3 py-1.5 {{ $badge[0] }}">{{ $badge[1] }}</span>

                <div class="mt-2">
                    @if($req->stage === 'revisi')
                        <form action="{{ route('portal.requests.resubmit', $req) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2 sm:justify-end">
                            @csrf
                            <input type="file" name="support_file" required accept=".pdf,.jpg,.jpeg,.png" class="text-xs w-full sm:w-48" aria-label="Berkas perbaikan">
                            <button class="btn-danger btn-sm">Kirim Ulang</button>
                        </form>
                    @elseif($req->stage === 'selesai' && $req->document)
                        <a href="{{ $req->document->fileUrl(true) }}" target="_blank" rel="noopener" class="btn-primary btn-sm"><x-nav-icon name="download" :size="14" /> Unduh E-Dokumen</a>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <x-empty-state title="Belum ada riwayat pengajuan" text="Ajukan surat keterangan atau legalisir dari rumah, tanpa antre di loket." action="Ajukan Dokumen" :href="route('portal.requests.create')" />
    @endforelse
</div>

<div class="mt-4">{{ $requests->links() }}</div>
@endsection
