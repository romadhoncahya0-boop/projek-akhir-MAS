<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Akun' }} — MAS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-ink min-h-screen flex flex-col">
    <header class="bg-navy text-white">
        <div class="max-w-5xl mx-auto px-4 h-14 flex items-center">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <x-brand-mark :size="30" />
                <span class="font-semibold tracking-wide">MAS</span>
                <span class="hidden sm:block text-sm text-white/60 border-l border-white/20 pl-2.5">Manajemen Akademik Siswa</span>
            </a>
        </div>
    </header>
    <main class="flex-1 flex items-start justify-center px-4 py-10">
        <div class="w-full max-w-md bg-white border border-line rounded p-6">
            {{ $slot }}
        </div>
    </main>
    <footer class="text-center text-xs text-muted pb-6">© {{ date('Y') }} {{ config('app.name') }}</footer>
</body>
</html>
