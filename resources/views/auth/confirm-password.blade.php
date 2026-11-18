<x-guest-layout>
    <x-slot name="title">Konfirmasi Kata Sandi</x-slot>
    <h1 class="font-heading text-xl font-semibold text-navy">Konfirmasi kata sandi</h1>
    <p class="text-sm text-muted mt-2 leading-relaxed">Ini adalah area yang dilindungi. Masukkan kata sandi Anda untuk melanjutkan.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-4">
        @csrf
        <div>
            <label for="password" class="label">Kata sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="input">
            @error('password')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>
        <button class="btn-primary w-full">Konfirmasi</button>
    </form>
</x-guest-layout>
