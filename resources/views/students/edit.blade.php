@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<x-page-header title="Edit Data Siswa" :subtitle="$student->name.' · NISN '.$student->nisn" />

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 panel p-6 md:p-8">
        <form action="{{ route('students.update', $student) }}" method="POST">
            @include('students._form')
        </form>
    </div>
    <aside class="space-y-4">
        <div class="panel p-5">
            <h2 class="font-heading text-base font-semibold text-navy mb-3">Ringkasan</h2>
            <dl class="divide-y divide-line text-sm">
                <div class="flex justify-between py-2"><dt class="text-muted">Dokumen</dt><dd class="font-medium text-navy tnum">{{ $student->documents()->count() }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-muted">Pengajuan</dt><dd class="font-medium text-navy tnum">{{ $student->documentRequests()->count() }}</dd></div>
                <div class="flex justify-between py-2"><dt class="text-muted">Terdaftar</dt><dd class="font-medium text-navy">{{ $student->created_at?->translatedFormat('d M Y') ?? '—' }}</dd></div>
            </dl>
        </div>
        <div class="rounded-xl bg-softblue p-5 text-sm text-navy/80 leading-relaxed">
            Mengubah NISN atau nama tidak memutus hubungan dengan akun login maupun dokumen yang sudah ada.
        </div>
    </aside>
</div>
@endsection
