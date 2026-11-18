@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<x-page-header title="Data Siswa" subtitle="Kelola data induk siswa dan akun login Portal Akademik.">
    <x-slot name="actions">
        <a href="{{ route('students.create') }}" class="btn-primary"><x-nav-icon name="user-plus" :size="16" /> Tambah Siswa</a>
    </x-slot>
</x-page-header>

<div class="stat-strip">
    <x-stat-card label="Total Siswa" :value="$counts['total']" hint="Seluruh data induk" />
    <x-stat-card label="Aktif" :value="$counts['aktif']" :href="route('students.index', ['status' => 'aktif'])" />
    <x-stat-card label="Lulus" :value="$counts['lulus']" :href="route('students.index', ['status' => 'lulus'])" />
    <x-stat-card label="Pindah" :value="$counts['pindah']" :href="route('students.index', ['status' => 'pindah'])" />
</div>

<div class="panel">
    <form method="GET" class="flex flex-wrap gap-3 p-4 border-b border-line">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau NISN" class="input flex-1 min-w-[200px]">
        <select name="status" class="input w-full sm:w-44">
            <option value="">Semua Status</option>
            <option value="aktif" @selected(request('status')=='aktif')>Aktif</option>
            <option value="lulus" @selected(request('status')=='lulus')>Lulus</option>
            <option value="pindah" @selected(request('status')=='pindah')>Pindah</option>
        </select>
        <button class="btn-secondary w-full sm:w-auto">Terapkan</button>
        @if(request('search') || request('status'))
            <a href="{{ route('students.index') }}" class="btn-secondary w-full sm:w-auto">Atur Ulang</a>
        @endif
    </form>

    <div class="overflow-x-auto">
        <table class="rtable w-full min-w-[720px] text-sm">
            <thead class="bg-canvas text-muted text-xs border-b border-line">
                <tr>
                    <th class="text-left px-4 py-3">NISN</th>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Kelas</th>
                    <th class="text-left px-4 py-3">Tahun Masuk</th>
                    <th class="text-left px-4 py-3">Akun</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse($students as $student)
                    @php
                        $badge = match($student->status) {
                            'lulus' => ['bg-success/10 text-success', 'Lulus'],
                            'pindah' => ['bg-danger/10 text-danger', 'Pindah'],
                            default => ['bg-softblue text-brand', 'Aktif'],
                        };
                    @endphp
                    <tr class="hover:bg-canvas">
                        <td class="px-4 py-3 tnum" data-label="NISN">{{ $student->nisn }}</td>
                        <td class="px-4 py-3 font-medium text-ink" data-label="Nama">{{ $student->name }}</td>
                        <td class="px-4 py-3" data-label="Kelas">{{ $student->class ?? '—' }}</td>
                        <td class="px-4 py-3 text-muted" data-label="Tahun Masuk">{{ $student->entry_year ?? '—' }}</td>
                        <td class="px-4 py-3" data-label="Akun">
                            @if($student->user_id)
                                <span class="badge bg-softblue text-brand">Terhubung</span>
                            @else
                                <span class="text-muted">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3" data-label="Status"><span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span></td>
                        <td class="px-4 py-3" data-label="Aksi">
                            <div class="flex gap-2">
                                <a href="{{ route('students.edit', $student) }}" class="icon-btn" title="Ubah data" aria-label="Ubah data {{ $student->name }}"><x-nav-icon name="edit" :size="14" /></a>
                                <form action="{{ route('students.destroy', $student) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus data siswa ini? Semua dokumen terkait juga akan terhapus.')">
                                    @csrf @method('DELETE')
                                    <button class="icon-btn hover:!bg-danger/10" title="Hapus" aria-label="Hapus {{ $student->name }}"><x-nav-icon name="trash" :size="14" /></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7"><x-empty-state title="Data siswa tidak ditemukan" text="Coba ubah kata pencarian atau filter status." /></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-4 border-t border-line">{{ $students->links() }}</div>
</div>
@endsection
