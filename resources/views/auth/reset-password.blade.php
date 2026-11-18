<x-guest-layout>
    <x-slot name="title">Kata Sandi Baru</x-slot>
    <h1 class="font-heading text-xl font-semibold text-navy">Buat kata sandi baru</h1>
    <p class="text-sm text-muted mt-2 leading-relaxed">Gunakan kata sandi minimal 8 karakter yang tidak dipakai di tempat lain.</p>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4" x-data="{show:false}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="input">
            @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <div class="flex justify-between mb-1.5">
                <label for="password" class="block text-sm font-medium text-ink">Kata sandi baru</label>
                <button type="button" @click="show=!show" class="text-xs text-brand" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button>
            </div>
            <input id="password" :type="show ? 'text' : 'password'" name="password" required autocomplete="new-password" class="input">
            @error('password')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="password_confirmation" class="label">Ulangi kata sandi baru</label>
            <input id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation" required autocomplete="new-password" class="input">
            @error('password_confirmation')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>

        <button class="btn-primary w-full">Simpan kata sandi baru</button>
    </form>
</x-guest-layout>
