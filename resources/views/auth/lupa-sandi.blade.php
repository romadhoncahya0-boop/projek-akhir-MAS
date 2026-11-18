<x-guest-layout>
    <x-slot name="title">Lupa Kata Sandi</x-slot>
    @if(session('sent'))
        <h1 class="text-lg font-semibold text-navy">Permintaan sudah diterima</h1>
        <p class="text-sm text-muted mt-2 leading-relaxed">Jika data yang Anda isi sesuai, Tata Usaha akan menyiapkan kata sandi sementara. Temui Tata Usaha dengan membawa kartu pelajar untuk mengambilnya. Anda akan diminta membuat kata sandi baru saat masuk.</p>
        <a href="{{ route('login') }}" class="btn-primary w-full mt-5">Kembali ke halaman masuk</a>
    @else
        <h1 class="text-lg font-semibold text-navy">Lupa kata sandi</h1>
        <p class="text-sm text-muted mt-1">Isi data berikut agar Tata Usaha dapat memastikan ini benar Anda.</p>
        <form method="POST" action="{{ route('forgot.siswa.store') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <label for="nisn" class="label">NISN</label>
                <input id="nisn" type="text" name="nisn" value="{{ old('nisn') }}" required inputmode="numeric" autocomplete="off" class="input">
                @error('nisn')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="name" class="label">Nama lengkap</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="off" class="input">
                <p class="text-xs text-muted mt-1">Tulis persis seperti pada data sekolah.</p>
                @error('name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="note" class="label">Catatan (opsional)</label>
                <input id="note" type="text" name="note" value="{{ old('note') }}" maxlength="200" class="input">
            </div>
            <button class="btn-primary w-full">Kirim permintaan</button>
        </form>
        <p class="text-xs text-muted mt-4">Akun staf atau administrator yang lupa kata sandi dapat menghubungi Administrator sistem.</p>
        <a href="{{ route('login') }}" class="block text-center text-sm text-brand mt-3 hover:underline">Kembali ke halaman masuk</a>
    @endif
</x-guest-layout>
