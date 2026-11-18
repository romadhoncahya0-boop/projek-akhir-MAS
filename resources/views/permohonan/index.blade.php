@extends('layouts.app')

@section('title', 'Manajemen Permohonan')

@section('content')
<x-page-header title="Manajemen Permohonan" subtitle="Pusat verifikasi berkas persyaratan siswa dan penerbitan dokumen akademik resmi dengan tanda tangan elektronik." />

<div class="stat-strip">
    <x-stat-card label="Menunggu Verifikasi" :value="$stats['menunggu_verifikasi']" :href="route('permohonan.index', ['stage' => 'diajukan'])" />
    <x-stat-card label="Perlu Revisi Siswa" :value="$stats['perlu_revisi']" tone="text-warning" :href="route('permohonan.index', ['stage' => 'revisi'])" />
    <x-stat-card label="Menunggu TTE Kepsek" :value="$stats['menunggu_tte']" :href="route('permohonan.index', ['stage' => 'menunggu_tte'])" />
    <x-stat-card label="Selesai Hari Ini" :value="$stats['selesai_hari_ini']" />
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-4">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, NISN, atau nomor tiket"
           class="input flex-1 min-w-[220px]">
    <select name="stage" class="input w-full sm:w-52">
        <option value="">Semua Status</option>
        <option value="diajukan" @selected(request('stage')=='diajukan')>Menunggu Verifikasi</option>
        <option value="revisi" @selected(request('stage')=='revisi')>Perlu Revisi</option>
        <option value="menunggu_tte" @selected(request('stage')=='menunggu_tte')>Menunggu TTE</option>
    </select>
    <button class="btn-secondary w-full sm:w-auto">Terapkan</button>
    @if(request('search') || request('stage'))
        <a href="{{ route('permohonan.index') }}" class="btn-secondary w-full sm:w-auto">Atur Ulang</a>
    @endif
</form>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel self-start">
        <div class="panel-head">
            <h2 class="text-sm font-semibold text-navy">Antrean permohonan <span class="text-muted font-normal">({{ $queue->count() }} dari {{ $queue->total() }})</span></h2>
        </div>
        <div class="divide-y divide-line">
            @forelse($queue as $item)
                <a href="{{ route('permohonan.index', array_merge(request()->except('page'), ['selected' => $item->id])) }}"
                   class="block p-4 hover:bg-canvas {{ $selected && $selected->id === $item->id ? 'bg-softblue/40' : '' }}">
                    <div class="flex items-start justify-between gap-3">
                        @if($item->fileIsImage('support') && $item->fileExists('support'))
                            <img src="{{ $item->fileUrl('support') }}" alt="Lampiran {{ $item->ticket_number }}" loading="lazy"
                                 style="width:3rem;height:3rem;object-fit:cover;border-radius:.5rem;flex-shrink:0" class="border border-line bg-canvas"
                                 onerror="this.style.display='none'">
                        @endif
                        <div class="flex-1 min-w-0">
                            <p class="text-brand text-xs font-semibold">{{ $item->ticket_number }}</p>
                            <p class="font-medium text-ink text-sm mt-0.5">{{ $item->student->name }}</p>
                            <p class="text-xs text-muted">NIS: {{ $item->student->nisn }} • {{ $item->student->class }}</p>
                            <p class="text-xs text-muted mt-1">{{ $item->service_name }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            @php
                                $badge = match($item->stage) {
                                    'revisi' => ['text-warning', 'Menunggu Siswa'],
                                    'menunggu_tte' => ['text-brand', 'Siap TTD'],
                                    default => ['text-muted', 'Perlu Verifikasi'],
                                };
                            @endphp
                            <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                            <p class="text-[11px] text-muted mt-2">{{ $item->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                </a>
            @empty
                <p class="p-8 text-center text-sm text-muted">Tidak ada permohonan dalam antrean.</p>
            @endforelse
        </div>
        <div class="p-4 border-t border-line">{{ $queue->links() }}</div>
    </div>

    <div id="detail" class="space-y-4 scroll-mt-24"
         x-data
         x-init="if (window.innerWidth < 1024 && new URLSearchParams(location.search).has('selected')) { $nextTick(() => $el.scrollIntoView()) }">
        @if($selected)
            <div class="panel p-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-brand text-sm font-semibold">{{ $selected->ticket_number }}</p>
                    @php
                        $stageBadge = match($selected->stage) {
                            'revisi' => ['text-warning', 'Perlu Revisi'],
                            'menunggu_tte' => ['text-brand', 'Menunggu TTE'],
                            'selesai' => ['text-success', 'Selesai'],
                            'ditolak' => ['text-danger', 'Ditolak'],
                            default => ['text-muted', 'Menunggu Verifikasi'],
                        };
                    @endphp
                    <span class="badge {{ $stageBadge[0] }}">{{ $stageBadge[1] }}</span>
                </div>
                <h3 class="font-semibold text-navy mb-3">Detail permohonan</h3>

                <div class="flex items-center gap-3 mb-4 pb-4 border-b border-line">
                    <div class="w-10 h-10 rounded-full bg-navy text-white flex items-center justify-center font-semibold text-sm shrink-0">
                        {{ strtoupper(substr($selected->student->name,0,2)) }}
                    </div>
                    <div>
                        <p class="font-medium text-ink text-sm">{{ $selected->student->name }}</p>
                        <p class="text-xs text-muted">NISN: {{ $selected->student->nisn }} • {{ $selected->student->class }} ({{ ucfirst($selected->student->status) }})</p>
                    </div>
                </div>

                <dl class="text-xs space-y-2 mb-4">
                    <div class="flex justify-between gap-4"><dt class="text-muted shrink-0">Layanan</dt><dd class="font-medium text-ink text-right">{{ $selected->service_name }}</dd></div>
                    <div class="flex justify-between gap-4"><dt class="text-muted shrink-0">Tujuan</dt><dd class="font-medium text-ink text-right">{{ $selected->purpose }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Jumlah Berkas</dt><dd class="font-medium text-ink">{{ $selected->physical_copies }} Fisik{{ $selected->digital_copy ? ' + Digital' : '' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-muted">Batas Waktu SLA</dt>
                        <dd class="font-medium {{ $selected->sla_due_at && $selected->sla_due_at->isPast() ? 'text-danger' : 'text-ink' }}">
                            {{ $selected->sla_due_at?->diffForHumans() ?? '—' }}
                        </dd>
                    </div>
                </dl>

                <div class="mb-4" x-data="{ preview: null }" @keydown.escape.window="preview = null">
                    <p class="text-xs font-semibold text-ink mb-2">Lampiran Berkas Siswa</p>
                    <div class="space-y-3">
                        @foreach(['support' => 'Berkas Pendukung', 'additional' => 'Berkas Tambahan'] as $kind => $label)
                            @continue($kind === 'additional' && ! $selected->additional_file_path)
                            @php
                                $url = $selected->fileUrl($kind);
                                $ok = $selected->fileExists($kind);
                                $isImg = $selected->fileIsImage($kind);
                            @endphp
                            <div class="border border-line rounded-lg p-3">
                                <p class="text-xs font-medium text-ink mb-2">{{ $label }}</p>
                                @if(! $ok)
                                    <div class="text-xs text-danger bg-danger/10 rounded-lg px-3 py-3">
                                        Berkas tidak ditemukan di penyimpanan server. Minta siswa mengunggah ulang melalui <em>Minta Revisi</em>.
                                    </div>
                                @elseif($isImg)
                                    <div class="relative" x-data="{ failed: false }"
                                         x-init="$nextTick(() => { const i = $el.querySelector('img'); if (i && i.complete && i.naturalWidth === 0) failed = true })">
                                        <button type="button" x-show="!failed" @click="preview = '{{ $url }}'" class="block w-full rounded-lg overflow-hidden border border-line bg-canvas" title="Klik untuk memperbesar">
                                            <img src="{{ $url }}" alt="{{ $label }} — {{ $selected->student->name }}" loading="lazy"
                                                 style="width:100%;height:11rem;object-fit:cover" x-on:error="failed = true">
                                        </button>
                                        <p x-show="failed" x-cloak class="text-xs text-danger bg-danger/10 rounded-lg px-3 py-3">Gambar gagal dimuat. Coba muat ulang halaman atau unduh berkasnya.</p>
                                    </div>
                                    <div class="flex flex-wrap gap-2 mt-2">
                                        <button type="button" @click="preview = '{{ $url }}'" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Perbesar</button>
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Buka di tab baru</a>
                                        <a href="{{ $selected->fileUrl($kind, true) }}" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Unduh</a>
                                    </div>
                                @else
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs text-muted inline-flex items-center gap-1.5"><x-nav-icon name="paperclip" :size="13" class="" /> Dokumen PDF</span>
                                        <a href="{{ $url }}" target="_blank" rel="noopener" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Buka</a>
                                        <a href="{{ $selected->fileUrl($kind, true) }}" class="text-xs px-3 py-1.5 rounded-lg border border-line hover:bg-canvas">Unduh</a>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div x-show="preview" x-cloak @click.self="preview = null"
                         style="position:fixed;inset:0;z-index:60;background:rgba(0,0,0,.75);display:flex;align-items:center;justify-content:center;padding:1rem">
                        <div style="position:relative;max-width:min(92vw,900px);max-height:92vh">
                            <button type="button" @click="preview = null" aria-label="Tutup pratinjau"
                                    style="position:absolute;top:-.5rem;right:-.5rem;width:2rem;height:2rem;border-radius:9999px;background:#fff;font-size:1.1rem;line-height:1;box-shadow:0 1px 4px rgba(0,0,0,.4)">×</button>
                            <img :src="preview" alt="Pratinjau berkas siswa" style="max-width:100%;max-height:92vh;object-fit:contain;border-radius:.5rem;background:#fff">
                        </div>
                    </div>
                </div>

                @if($selected->stage === 'diajukan')
                    <form action="{{ route('permohonan.approve', $selected) }}" method="POST" class="mb-2">
                        @csrf
                        <button class="btn-primary w-full">Setujui &amp; Teruskan ke Kepala Sekolah</button>
                    </form>
                    <details class="mb-2">
                        <summary class="text-sm px-4 py-2.5 rounded-lg border border-line text-ink hover:bg-canvas cursor-pointer text-center list-none">Minta Revisi Siswa</summary>
                        <form action="{{ route('permohonan.revisi', $selected) }}" method="POST" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="tu_notes" required rows="2" placeholder="Jelaskan berkas apa yang perlu diperbaiki..." class="w-full text-xs rounded-lg border-line"></textarea>
                            <button class="w-full text-xs px-3 py-2 rounded-lg bg-warning text-white">Kirim Permintaan Revisi</button>
                        </form>
                    </details>
                    <details>
                        <summary class="text-xs text-danger cursor-pointer list-none text-center">Tolak Permohonan</summary>
                        <form action="{{ route('permohonan.reject', $selected) }}" method="POST" class="mt-2 space-y-2">
                            @csrf
                            <textarea name="tu_notes" required rows="2" placeholder="Alasan penolakan..." class="w-full text-xs rounded-lg border-line"></textarea>
                            <button class="w-full text-xs px-3 py-2 rounded-lg bg-danger text-white">Tolak</button>
                        </form>
                    </details>
                @elseif($selected->stage === 'menunggu_tte')
                    <form action="{{ route('permohonan.sign', $selected) }}" method="POST">
                        @csrf
                        <button class="btn-primary w-full">Tanda Tangani &amp; Terbitkan Dokumen</button>
                    </form>
                    <p class="text-[11px] text-muted mt-2 text-center">*Disimulasikan lewat akun Tata Usaha karena role Kepala Sekolah belum tersedia.</p>
                @elseif($selected->stage === 'revisi')
                    <p class="text-xs text-warning bg-warning/10 rounded-lg px-3 py-2">Menunggu siswa mengunggah ulang berkas perbaikan.</p>
                @endif
            </div>

            <div class="bg-navy text-white rounded-xl p-4">
                <p class="text-xs font-semibold text-white/70 mb-2">Jejak tanda tangan</p>
                <p class="text-xs text-white/70 mb-3">Saat dokumen ditandatangani, sistem menyimpan hash SHA-256 sebagai penanda.</p>
                <div class="text-[11px] space-y-1 text-white/60">
                    <div class="flex justify-between"><span>Algoritma</span><span>SHA-256</span></div>
                    @if($selected->signed_at)
                        <div class="flex justify-between"><span>Ditandatangani</span><span>{{ $selected->signed_at->format('d-m-Y H:i') }} WIB</span></div>
                        <div class="flex justify-between"><span>Hash</span><span>{{ substr($selected->signed_hash,0,10) }}…</span></div>
                    @endif
                </div>
            </div>
        @else
            <div class="panel p-8 text-center text-sm text-muted">
                Pilih salah satu permohonan di daftar antrean untuk melihat detail verifikasi.
            </div>
        @endif
    </div>
</div>
@endsection
