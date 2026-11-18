<x-guest-layout>
    <x-slot name="title">Verifikasi Email</x-slot>
    <h1 class="font-heading text-xl font-semibold text-navy">Verifikasi alamat email</h1>
    <p class="text-sm text-muted mt-2 leading-relaxed">Kami sudah mengirim tautan verifikasi ke email Anda. Klik tautan tersebut untuk melanjutkan. Jika belum menerima email, kami dapat mengirim ulang.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded border border-success/25 bg-success/10 px-4 py-3 text-sm text-success">Tautan verifikasi baru sudah dikirim ke alamat email Anda.</div>
    @endif

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="btn-primary">Kirim ulang email verifikasi</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-muted hover:text-ink">Keluar</button>
        </form>
    </div>
</x-guest-layout>
