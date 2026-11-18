@extends('layouts.app')
@section('title', 'Manajemen Pengguna')
@section('content')
<x-page-header title="Manajemen Pengguna" subtitle="Kelola akun admin, petugas TU, dan siswa yang dapat masuk ke sistem.">
    <x-slot name="actions">
        <a href="{{ route('users.create') }}" class="btn-primary"><x-nav-icon name="plus" :size="15" /> Tambah Pengguna</a>
    </x-slot>
</x-page-header>

<div class="panel">
    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-line">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email" class="input flex-1 min-w-[180px]">
        <select name="role" class="input w-full sm:w-44">
            <option value="">Semua peran</option>
            @foreach($roles as $v => $l)<option value="{{ $v }}" @selected(request('role') === $v)>{{ $l }}</option>@endforeach
        </select>
        <button class="btn-secondary w-full sm:w-auto">Terapkan</button>
        @if(request('search') || request('role'))
            <a href="{{ route('users.index') }}" class="btn-secondary w-full sm:w-auto">Atur Ulang</a>
        @endif
    </form>
    <div class="overflow-x-auto">
        <table class="rtable w-full min-w-[680px] text-sm">
            <thead class="bg-canvas text-muted text-xs border-b border-line">
                <tr><th class="text-left px-4 py-3">Nama</th><th class="text-left px-4 py-3">Email</th><th class="text-left px-4 py-3">Peran</th><th class="text-left px-4 py-3">NISN Terhubung</th><th class="text-left px-4 py-3">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($users as $u)
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3 font-medium text-ink" data-label="Nama"><span>{{ $u->name }}@if($u->id === auth()->id()) <span class="text-xs text-muted">(Anda)</span>@endif</span></td>
                        <td class="px-4 py-3 text-muted break-all" data-label="Email">{{ $u->email }}</td>
                        <td class="px-4 py-3" data-label="Peran"><span class="badge bg-softblue text-brand">{{ $roles[$u->role] ?? $u->role }}</span></td>
                        <td class="px-4 py-3 text-muted tnum" data-label="NISN Terhubung">{{ $u->student ? $u->student->nisn : '-' }}</td>
                        <td class="px-4 py-3" data-label="Aksi">
                            <div class="flex gap-2">
                                <a href="{{ route('users.edit', $u) }}" class="icon-btn" title="Ubah" aria-label="Ubah {{ $u->name }}"><x-nav-icon name="edit" :size="14" /></a>
                                @if($u->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $u) }}" method="POST" data-name="{{ $u->name }}" onsubmit="return confirm('Hapus akun ' + this.dataset.name + '?')">@csrf @method('DELETE')
                                        <button class="icon-btn hover:!bg-danger/10" title="Hapus" aria-label="Hapus {{ $u->name }}"><x-nav-icon name="trash" :size="14" /></button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-empty-state title="Belum ada pengguna yang sesuai" text="Coba ubah kata pencarian atau filter peran." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-4 border-t border-line">{{ $users->links() }}</div>
</div>
@endsection
