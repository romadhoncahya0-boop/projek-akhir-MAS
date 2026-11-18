<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MAS — Manajemen Akademik Siswa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white text-ink">

<header class="bg-navy text-white">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <x-brand-mark :size="30" />
            <span class="font-semibold tracking-wide">MAS</span>
            <span class="hidden sm:block text-sm text-white/60 border-l border-white/20 pl-2.5">Manajemen Akademik Siswa</span>
        </a>
        <nav class="flex items-center gap-5 text-sm">
            <a href="#alur" class="hidden sm:block text-white/70 hover:text-white">Alur</a>
            <a href="#faq" class="hidden sm:block text-white/70 hover:text-white">FAQ</a>
            <a href="#kontak" class="hidden sm:block text-white/70 hover:text-white">Kontak</a>
            <a href="{{ route('login') }}" class="bg-white text-navy font-medium px-4 py-1.5 rounded hover:bg-softblue">Masuk</a>
        </nav>
    </div>
</header>

<section class="bg-canvas border-b border-line">
    <div class="max-w-5xl mx-auto px-4 py-12 md:py-16 grid md:grid-cols-3 gap-8 items-start">
        <div class="md:col-span-2">
            <h1 class="text-3xl font-semibold text-navy leading-snug">Layanan dokumen akademik siswa</h1>
            <p class="mt-3 text-ink/80 max-w-xl">
                Siswa dapat mengajukan surat keterangan atau legalisir, memantau prosesnya, dan mengunduh dokumen yang sudah diterbitkan.
                Tata Usaha memeriksa berkas dan memberi catatan jika ada yang perlu diperbaiki.
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('login') }}" class="btn-primary px-5 py-2.5">Masuk ke portal</a>
                <a href="#alur" class="btn-secondary px-5 py-2.5">Lihat alurnya</a>
            </div>
        </div>
        <dl class="panel divide-y divide-line text-sm">
            <div class="flex justify-between px-4 py-3"><dt class="text-muted">Siswa aktif</dt><dd class="font-semibold text-navy tnum">{{ $totalStudents }}</dd></div>
            <div class="flex justify-between px-4 py-3"><dt class="text-muted">Dokumen terverifikasi</dt><dd class="font-semibold text-navy tnum">{{ $totalDocuments }}</dd></div>
            <div class="flex justify-between px-4 py-3"><dt class="text-muted">Permohonan selesai</dt><dd class="font-semibold text-navy tnum">{{ $totalDone }}</dd></div>
        </dl>
    </div>
</section>

<section id="alur" class="max-w-5xl mx-auto px-4 py-12">
    <h2 class="text-xl font-semibold text-navy">Alur pengajuan</h2>
    <ol class="mt-5 grid sm:grid-cols-2 lg:grid-cols-4 gap-px bg-line border border-line rounded overflow-hidden">
        @foreach([
            ['Ajukan', 'Pilih jenis dokumen, isi tujuan, dan unggah berkas pendukung.'],
            ['Verifikasi', 'Tata Usaha memeriksa berkas. Jika kurang, siswa menerima catatan revisi.'],
            ['Tanda tangan', 'Permohonan yang disetujui diteruskan untuk ditandatangani.'],
            ['Unduh', 'Dokumen muncul di menu Dokumen Saya dan siap diunduh.'],
        ] as $i => [$judul, $isi])
            <li class="bg-white p-4">
                <p class="text-xs text-muted">Langkah {{ $i + 1 }}</p>
                <p class="font-semibold text-navy mt-0.5">{{ $judul }}</p>
                <p class="text-sm text-ink/80 mt-1">{{ $isi }}</p>
            </li>
        @endforeach
    </ol>
</section>

<section class="bg-canvas border-y border-line">
    <div class="max-w-5xl mx-auto px-4 py-12 grid md:grid-cols-3 gap-8">
        <div class="md:col-span-2">
            <h2 class="text-xl font-semibold text-navy">Informasi layanan</h2>
            <dl class="mt-4 panel divide-y divide-line text-sm">
                <div class="grid sm:grid-cols-3 gap-1 px-4 py-3"><dt class="font-medium text-navy">Jam layanan loket</dt><dd class="sm:col-span-2 text-ink/80">{{ \App\Models\Setting::get('service_days') }}, {{ \App\Models\Setting::get('service_hours') }}. Pengajuan digital dapat dikirim kapan saja dan diproses pada jam kerja.</dd></div>
                <div class="grid sm:grid-cols-3 gap-1 px-4 py-3"><dt class="font-medium text-navy">Estimasi proses</dt><dd class="sm:col-span-2 text-ink/80">Sekitar {{ \App\Models\Setting::get('sla_days') }} hari kerja sejak berkas lengkap diterima.</dd></div>
                <div class="grid sm:grid-cols-3 gap-1 px-4 py-3"><dt class="font-medium text-navy">Berkas pendukung</dt><dd class="sm:col-span-2 text-ink/80">Format PDF, JPG, atau PNG. Pastikan hasil pindaian jelas dan tidak buram.</dd></div>
                <div class="grid sm:grid-cols-3 gap-1 px-4 py-3"><dt class="font-medium text-navy">Salinan fisik</dt><dd class="sm:col-span-2 text-ink/80">Diambil di loket Tata Usaha dengan membawa kartu pelajar.</dd></div>
            </dl>
        </div>
        <aside class="panel p-4 self-start text-sm">
            <p class="font-semibold text-navy">Belum punya akun?</p>
            <p class="text-ink/80 mt-1">Akun siswa dibuatkan oleh Tata Usaha. Datang ke loket atau hubungi kontak di bawah.</p>
        </aside>
    </div>
</section>

@php $videoFile = public_path('media/panduan.mp4'); @endphp
@if(file_exists($videoFile))
<section class="max-w-3xl mx-auto px-4 pt-12">
    <h2 class="text-xl font-semibold text-navy mb-4">Video panduan</h2>
    <video controls preload="metadata" class="w-full rounded border border-line bg-black" @if(file_exists(public_path('media/panduan.jpg'))) poster="{{ asset('media/panduan.jpg') }}" @endif>
        <source src="{{ asset('media/panduan.mp4') }}" type="video/mp4">
        Peramban Anda belum mendukung pemutaran video.
    </video>
</section>
@endif

<section id="faq" class="max-w-3xl mx-auto px-4 py-12">
    <h2 class="text-xl font-semibold text-navy mb-4">Pertanyaan umum</h2>
    <div class="border border-line rounded divide-y divide-line">
        @foreach([
            ['Bagaimana jika berkas saya diminta revisi?', 'Buka Riwayat Pengajuan, baca catatan dari Tata Usaha, lalu unggah ulang berkas perbaikan dari halaman itu.'],
            ['Di mana saya mengunduh dokumen yang sudah selesai?', 'Di menu Dokumen Saya setelah masuk ke portal. Dokumen juga bisa diunduh dari Riwayat Pengajuan.'],
            ['Saya lupa kata sandi, apa yang harus dilakukan?', 'Pilih "Lupa kata sandi?" di halaman masuk, isi NISN dan nama, lalu temui Tata Usaha untuk mengambil kata sandi sementara.'],
            ['Berapa ukuran maksimal berkas yang diunggah?', 'Maksimal 5 MB per berkas untuk pengajuan, dengan format PDF, JPG, atau PNG.'],
        ] as [$q, $a])
            <details class="group">
                <summary class="flex justify-between items-center gap-4 px-4 py-3 cursor-pointer font-medium text-navy list-none">
                    {{ $q }}<span class="text-muted group-open:rotate-45 shrink-0">+</span>
                </summary>
                <p class="px-4 pb-4 text-sm text-ink/80">{{ $a }}</p>
            </details>
        @endforeach
    </div>
</section>

<footer id="kontak" class="bg-navy text-white/70 text-sm">
    <div class="max-w-5xl mx-auto px-4 py-8 grid sm:grid-cols-2 gap-6">
        <div>
            <p class="text-white font-semibold">{{ \App\Models\Setting::get('school_name') }}</p>
            <p class="mt-1">{{ \App\Models\Setting::get('school_address') }}</p>
        </div>
        <div>
            <p class="text-white font-semibold">Kontak Tata Usaha</p>
            <p class="mt-1">{{ \App\Models\Setting::get('contact_email') }}@if(\App\Models\Setting::get('contact_phone')) · {{ \App\Models\Setting::get('contact_phone') }}@endif</p>
        </div>
    </div>
    <div class="border-t border-white/10 py-3 text-center text-xs text-white/50">© {{ date('Y') }} {{ \App\Models\Setting::get('school_name') }}</div>
</footer>
</body>
</html>
