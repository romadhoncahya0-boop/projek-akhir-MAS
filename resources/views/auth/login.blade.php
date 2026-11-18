<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk — MAS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-ink min-h-screen flex flex-col">
    <header class="bg-navy text-white">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <x-brand-mark :size="30" />
                <span class="font-semibold tracking-wide">MAS</span>
                <span class="hidden sm:block text-sm text-white/60 border-l border-white/20 pl-2.5">Manajemen Akademik Siswa</span>
            </a>
            <a href="{{ url('/') }}" class="text-sm text-white/70 hover:text-white">Beranda</a>
        </div>
    </header>

    <main class="flex-1 flex items-start justify-center px-4 py-10" x-data="{show:false}">
        <div class="w-full max-w-sm">
            <div class="bg-white border border-line rounded">
                <div class="px-6 py-4 border-b border-line bg-canvas">
                    <h1 class="text-lg font-semibold text-navy">Masuk</h1>
                    <p class="text-sm text-muted">Gunakan akun siswa atau akun staf Tata Usaha.</p>
                </div>

                <form method="POST" action="{{ route('login') }}" class="p-6 space-y-4">
                    @csrf

                    @if (session('status'))
                        <p class="text-sm text-success">{{ session('status') }}</p>
                    @endif

                    <div>
                        <label for="email" class="label">Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus class="input">
                        @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <div class="flex justify-between mb-1">
                            <label for="password" class="text-sm font-medium text-ink">Kata sandi</label>
                            <button type="button" @click="show=!show" class="text-xs text-brand hover:underline" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button>
                        </div>
                        <input id="password" name="password" required :type="show ? 'text' : 'password'" type="password" class="input">
                        @error('password')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-muted">
                            <input type="checkbox" name="remember" class="rounded-sm border-line text-brand">
                            Ingat saya
                        </label>
                        @if (Route::has('forgot.siswa'))
                            <a href="{{ route('forgot.siswa') }}" class="text-brand hover:underline">Lupa kata sandi?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-primary w-full py-2.5">Masuk</button>
                </form>
            </div>
            <p class="text-xs text-muted mt-4">Staf yang lupa kata sandi dapat menghubungi Administrator.</p>
        </div>
    </main>
</body>
</html>
