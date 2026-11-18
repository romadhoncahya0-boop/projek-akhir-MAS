<x-guest-layout>
    <x-slot name="title">Buat Kata Sandi Baru</x-slot>
    <div x-data="{show:false}">
        <h1 class="text-lg font-semibold text-navy">Buat kata sandi baru</h1>
        <p class="text-sm text-muted mt-1">Halo, {{ auth()->user()->name }}. Kata sandi Anda baru saja diatur ulang oleh Tata Usaha. Buat kata sandi baru yang hanya Anda yang tahu.</p>
        <form method="POST" action="{{ route('password.force.update') }}" class="mt-5 space-y-4">
            @csrf
            <div>
                <div class="flex justify-between mb-1">
                    <label for="password" class="text-sm font-medium text-ink">Kata sandi baru</label>
                    <button type="button" @click="show=!show" class="text-xs text-brand hover:underline" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button>
                </div>
                <input id="password" :type="show ? 'text' : 'password'" name="password" required minlength="8" autocomplete="new-password" class="input">
                <p class="text-xs text-muted mt-1">Minimal 8 karakter.</p>
                @error('password')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
                <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="input">
            </div>
            <button class="btn-primary w-full">Simpan kata sandi baru</button>
        </form>
        <form method="POST" action="{{ route('logout') }}" class="mt-3 text-center">@csrf
            <button class="text-xs text-muted hover:text-ink">Keluar</button>
        </form>
    </div>
</x-guest-layout>
