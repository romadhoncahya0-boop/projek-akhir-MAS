<x-guest-layout>
    <x-slot name="title">Atur Ulang Kata Sandi</x-slot>
    <h1 class="font-heading text-xl font-semibold text-navy">Atur ulang kata sandi</h1>
    <p class="text-sm text-muted mt-2 leading-relaxed">Masukkan email akun Anda. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>

    @if (session('status'))
        <div class="mt-4 rounded border border-success/25 bg-success/10 px-4 py-3 text-sm text-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
            @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full">Kirim tautan atur ulang</button>
    </form>

    <p class="text-xs text-muted mt-5 leading-relaxed">Siswa yang lupa kata sandi dapat meminta bantuan Tata Usaha melalui <a href="{{ route('forgot.siswa') }}" class="text-brand hover:underline">halaman lupa kata sandi siswa</a>.</p>
    <a href="{{ route('login') }}" class="block text-center text-sm text-brand mt-4 hover:underline">Kembali ke halaman masuk</a>
</x-guest-layout>
