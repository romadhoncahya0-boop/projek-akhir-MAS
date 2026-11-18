<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') — MAS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas font-sans text-ink antialiased"
      x-effect="document.body.classList.toggle('overflow-hidden', drawer)"
      @keydown.escape.window="drawer=false"
      x-data="{ drawer:false, collapsed: (()=>{try{return localStorage.getItem('sb')==='1'}catch(e){return false}})(), toggle(){ this.collapsed=!this.collapsed; try{localStorage.setItem('sb',this.collapsed?'1':'0')}catch(e){} } }">
@php
    $user = auth()->user();
    $role = $user->role;
    $isSiswa = $role === 'siswa';
    $firstName = explode(' ', trim($user->name))[0];

    $pendingReset = $isSiswa ? 0 : \App\Models\PasswordResetRequest::where('status', 'pending')->count();
    $notifs = collect();
    if ($isSiswa && $user->student) {
        foreach ($user->student->documentRequests()->whereIn('stage', ['revisi', 'selesai'])->latest('updated_at')->take(5)->get() as $r) {
            if (! $user->wantsNotification($r->stage)) { continue; }
            $notifs->push(['text' => $r->stage === 'revisi' ? "{$r->ticket_number} perlu revisi" : "{$r->ticket_number} sudah selesai & siap diunduh", 'time' => $r->updated_at, 'href' => route('portal.requests.index'), 'tone' => $r->stage === 'revisi' ? 'danger' : 'success']);
        }
    } elseif (! $isSiswa) {
        if ($pendingReset && $user->wantsNotification('sandi')) {
            $notifs->push(['text' => $pendingReset.' siswa meminta atur ulang kata sandi', 'time' => now(), 'href' => route('reset-requests.index'), 'tone' => 'warning']);
        }
        foreach (! $user->wantsNotification('antrean') ? [] : \App\Models\DocumentRequest::with('student')->whereIn('stage', ['diajukan', 'menunggu_tte'])->latest()->take(5)->get() as $r) {
            $notifs->push(['text' => ($r->stage === 'diajukan' ? 'Menunggu verifikasi: ' : 'Menunggu TTE: ').$r->student->name, 'time' => $r->created_at, 'href' => route('permohonan.index', ['selected' => $r->id]), 'tone' => 'warning']);
        }
    }

    $nav = $isSiswa ? [
        'Beranda' => [['dashboard', 'Dashboard', 'home', 'dashboard']],
        'Layanan Akademik' => [
            ['portal.documents', 'Dokumen Saya', 'folder', 'portal.documents'],
            ['portal.requests.create', 'Ajukan Dokumen', 'file-plus', 'portal.requests.create'],
            ['portal.requests.index', 'Riwayat Pengajuan', 'clock', 'portal.requests.index'],
        ],
        'Akun' => [['profile.edit', 'Profil & Pengaturan', 'settings', 'profile.*']],
    ] : [
        'Beranda' => [['dashboard', 'Dashboard', 'home', 'dashboard']],
        'Administrasi' => [
            ['permohonan.index', 'Manajemen Permohonan', 'clipboard-check', 'permohonan.*'],
            ['documents.index', 'Verifikasi Dokumen', 'shield-check', 'documents.*'],
            ['students.index', 'Data Siswa', 'users', 'students.*'],
            ['reset-requests.index', 'Permintaan Kata Sandi', 'lock', 'reset-requests.*'],
        ],
        'Pengelolaan' => [
            ['reports.index', 'Laporan & Statistik', 'chart', 'reports.*'],
            ['profile.edit', 'Profil & Pengaturan', 'settings', 'profile.*'],
        ],
    ];
    if ($role === 'admin') {
        $nav['Pengelolaan'] = array_merge([['users.index', 'Manajemen Pengguna', 'users', 'users.*'], ['school-settings.edit', 'Pengaturan Sekolah', 'building', 'school-settings.*']], $nav['Pengelolaan']);
    }
@endphp

<div class="flex gap-4 lg:p-4 min-h-screen">
    <div x-show="drawer" x-cloak class="fixed inset-0 bg-black/50 z-40 lg:hidden" @click="drawer=false"></div>

    <aside class="app-sidebar fixed lg:sticky top-0 lg:top-4 z-50 lg:z-10 h-[100dvh] lg:h-[calc(100vh-2rem)] flex flex-col shrink-0 overflow-y-auto"
           :class="[drawer ? 'is-open' : '', collapsed ? 'lg:w-[76px]' : 'lg:w-64', 'w-64']">
        <div class="p-3 flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="brand-tile flex-1 min-w-0 flex items-center justify-center gap-2.5 px-3 py-3" :class="collapsed && !drawer ? 'lg:px-0' : ''">
                <x-brand-mark :size="28" />
                <span class="font-semibold text-white tracking-wide" x-show="!collapsed || drawer">MAS</span>
            </a>
            <button @click="drawer=false" class="lg:hidden p-2 text-white/70" aria-label="Tutup"><x-nav-icon name="x" :size="18" class="" /></button>
        </div>
        <nav class="flex-1 px-3 pb-3 text-sm">
            @foreach($nav as $group => $items)
                <p class="px-3 pb-1.5 pt-5 first:pt-2 text-xs font-medium text-white/45" x-show="!collapsed || drawer">{{ $group }}</p>
                @foreach($items as [$routeName, $label, $icon, $match])
                    @php $active = request()->routeIs($match); @endphp
                    <a href="{{ route($routeName) }}" title="{{ $label }}"
                       class="nav-link {{ $active ? 'is-active' : '' }}"
                       :class="collapsed && !drawer ? 'lg:justify-center' : ''">
                        <x-nav-icon :name="$icon" />
                        <span x-show="!collapsed || drawer" class="flex-1">{{ $label }}</span>
                        @if($routeName === 'reset-requests.index' && $pendingReset)<span class="text-[11px] min-w-[18px] h-[18px] px-1 bg-danger text-white text-center leading-[18px] rounded-full" x-show="!collapsed || drawer">{{ $pendingReset }}</span>@endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="p-3 border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="nav-link w-full" :class="collapsed && !drawer ? 'lg:justify-center' : ''" title="Keluar">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>
                    <span x-show="!collapsed || drawer" class="flex-1 text-left">Keluar</span>
                </button>
            </form>
            <p class="px-3 pt-2 text-xs text-white/45" x-show="!collapsed || drawer">Jam layanan TU<br>{{ \App\Models\Setting::get('service_days') }}, {{ \App\Models\Setting::get('service_hours') }}</p>
        </div>
    </aside>

    <div class="flex-1 min-w-0 flex flex-col">
        <header class="app-header sticky top-0 z-30">
            <div class="px-4 lg:px-2 h-16 flex items-center gap-3">
                <button class="lg:hidden p-2 -ml-2 text-white" @click="drawer=true" aria-label="Buka menu">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
                </button>
                <button class="hidden lg:block p-2 -ml-2 text-white/60 hover:text-white" @click="toggle()" aria-label="Ciutkan sidebar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h12M3 18h18"/></svg>
                </button>
                <a href="{{ route('dashboard') }}" class="lg:hidden flex items-center gap-2">
                    <x-brand-mark :size="28" />
                    <span class="font-semibold text-white">MAS</span>
                </a>

                <button type="button" @click="$dispatch('open-search')" class="search-pill hidden md:flex flex-1 max-w-sm items-center gap-2 px-4 py-2 text-sm text-left">
                    <span class="flex-1">Cari...</span>
                    <x-nav-icon name="search" :size="15" />
                </button>
                <button type="button" @click="$dispatch('open-search')" class="md:hidden w-9 h-9 flex items-center justify-center text-white ml-auto" aria-label="Cari">
                    <x-nav-icon name="search" :size="19" />
                </button>

                <div class="ml-auto flex items-center gap-1">
                    <div class="relative" x-data="{o:false}" @click.outside="o=false">
                        <button @click="o=!o" class="relative w-10 h-10 rounded-full hover:bg-white/10 flex items-center justify-center text-white" aria-label="Notifikasi">
                            <x-nav-icon name="bell" :size="19" />
                            @if($notifs->count())<span class="absolute top-1 right-1 min-w-[16px] h-4 px-1 bg-danger text-white text-[11px] leading-4 text-center rounded-full">{{ $notifs->count() }}</span>@endif
                        </button>
                        <div x-show="o" x-cloak class="fixed inset-x-3 top-[4.25rem] sm:absolute sm:inset-x-auto sm:right-0 sm:top-auto sm:mt-2 sm:w-80 bg-white text-ink border border-line rounded-xl shadow-lg z-40 overflow-hidden">
                            <div class="px-4 py-3 border-b border-line bg-canvas text-sm font-semibold text-white">Notifikasi</div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-line">
                                @forelse($notifs as $n)
                                    <a href="{{ $n['href'] }}" class="flex gap-3 px-4 py-3 hover:bg-canvas text-sm">
                                        <span class="mt-1.5 w-2 h-2 rounded-full shrink-0 {{ $n['tone'] === 'danger' ? 'bg-danger' : ($n['tone'] === 'success' ? 'bg-success' : 'bg-warning') }}"></span>
                                        <span><span class="block text-ink">{{ $n['text'] }}</span><span class="text-xs text-muted">{{ $n['time']->diffForHumans() }}</span></span>
                                    </a>
                                @empty
                                    <p class="px-4 py-6 text-sm text-muted text-center">Belum ada notifikasi baru.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="relative user-menu ml-1 pl-3" x-data="{o:false}" @click.outside="o=false">
                        <button @click="o=!o" class="flex items-center gap-2.5 py-1 pr-1">
                            <x-avatar :user="$user" :size="34" class="bg-white text-navy" />
                            <span class="hidden sm:block text-left leading-tight">
                                <span class="block text-sm text-white">{{ $user->name }}</span>
                                <span class="block text-[11px] text-white/55 capitalize">{{ $role }}</span>
                            </span>
                            <svg class="hidden sm:block text-white/60" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div x-show="o" x-cloak class="absolute right-0 mt-2 w-56 bg-white text-ink border border-line rounded-xl shadow-lg z-40 overflow-hidden">
                            <div class="px-4 py-3 border-b border-line">
                                <p class="text-sm font-medium truncate">{{ $user->name }}</p>
                                <p class="text-xs text-muted truncate">{{ $user->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-canvas">Pengaturan akun</a>
                            <form method="POST" action="{{ route('logout') }}">@csrf
                                <button class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-canvas border-t border-line">Keluar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 w-full max-w-7xl mx-auto px-4 lg:px-2 py-6">
            @if (session('success'))
                <div x-data="{s:true}" x-show="s" class="mb-5 flex items-start justify-between gap-3 rounded-xl border border-success/40 bg-success/10 px-4 py-3 text-sm text-success">
                    <span>{{ session('success') }}</span><button @click="s=false" class="text-success/70" aria-label="Tutup"><x-nav-icon name="x" :size="16" class="" /></button>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 rounded-xl border border-danger/40 bg-danger/10 px-4 py-3 text-sm text-danger">{{ session('error') }}</div>
            @endif
            @if ($errors->any() && ! request()->routeIs('portal.requests.create'))
                <div class="mb-5 rounded-xl border border-danger/40 bg-danger/10 px-4 py-3 text-sm text-danger">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>

        <footer class="px-4 lg:px-2 py-4 text-xs text-white/45 flex flex-wrap justify-between gap-x-6 gap-y-1">
            <span>© {{ date('Y') }} {{ \App\Models\Setting::get('school_name') }}</span>
            <span>{{ \App\Models\Setting::get('contact_email') }}@if(\App\Models\Setting::get('contact_phone')) · {{ \App\Models\Setting::get('contact_phone') }}@endif</span>
        </footer>
    </div>
</div>

<div x-data="{
        show:false, q:'', groups:[], loading:false, done:false, timer:null,
        open(){ this.show=true; this.$nextTick(()=>this.$refs.inp.focus()) },
        close(){ this.show=false },
        run(){
            clearTimeout(this.timer);
            if(this.q.trim().length<2){ this.groups=[]; this.done=false; return }
            this.loading=true;
            this.timer=setTimeout(async()=>{
                try{
                    const r=await fetch('{{ route('search') }}?q='+encodeURIComponent(this.q),{headers:{'Accept':'application/json'}});
                    this.groups=(await r.json()).groups; this.done=true;
                }catch(e){ this.groups=[]; this.done=true }
                this.loading=false;
            },250);
        }
     }"
     @open-search.window="open()"
     @keydown.window.ctrl.k.prevent="open()"
     @keydown.window.meta.k.prevent="open()"
     @keydown.escape.window="close()">
    <div x-show="show" x-cloak class="fixed inset-0 z-[60] bg-black/40" @click="close()"></div>
    <div x-show="show" x-cloak class="fixed z-[70] top-20 left-1/2 -translate-x-1/2 w-[92vw] max-w-xl bg-white border border-line rounded shadow-lg overflow-hidden" role="dialog" aria-label="Pencarian">
        <div class="flex items-center gap-3 px-4 border-b border-line">
            <x-nav-icon name="search" :size="18" class="text-muted" />
            <input x-ref="inp" x-model="q" @input="run()" type="text" placeholder="Ketik nama dokumen, nomor pengajuan, atau status"
                   class="flex-1 py-4 border-0 focus:ring-0 text-sm placeholder:text-muted/70">
            <button @click="close()" class="text-xs border border-line rounded-sm px-1.5 text-muted">Esc</button>
        </div>
        <div class="max-h-[60vh] overflow-y-auto">
            <p x-show="loading" class="px-5 py-6 text-sm text-muted">Mencari...</p>
            <p x-show="!loading && q.trim().length<2" class="px-5 py-6 text-sm text-muted">Mulai mengetik untuk mencari. Minimal dua karakter.</p>
            <p x-show="!loading && done && q.trim().length>=2 && groups.length===0" class="px-5 py-6 text-sm text-muted">Belum ada hasil yang sesuai dengan pencarian Anda.</p>
            <template x-for="g in groups" :key="g.label">
                <div>
                    <p class="px-5 pt-4 pb-1 text-xs font-semibold text-muted" x-text="g.label"></p>
                    <template x-for="it in g.items" :key="it.url + it.title + it.sub">
                        <a :href="it.url" class="block px-5 py-2.5 hover:bg-softblue">
                            <span class="block text-sm text-ink" x-text="it.title"></span>
                            <span class="block text-xs text-muted" x-text="it.sub"></span>
                        </a>
                    </template>
                </div>
            </template>
        </div>
    </div>
</div>
</body>
</html>
