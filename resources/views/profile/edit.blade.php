@extends('layouts.app')
@section('title', 'Profil & Pengaturan')

@section('content')
@php
    $status = session('status');
    $startTab = ($status === 'password-updated' || $errors->updatePassword->any()) ? 'keamanan' : ($status === 'notif-updated' ? 'notifikasi' : 'profil');
    $messages = ['profile-updated' => 'Profil Anda berhasil diperbarui.', 'password-updated' => 'Kata sandi berhasil diubah.', 'notif-updated' => 'Preferensi notifikasi berhasil disimpan.'];
@endphp

<x-page-header title="Profil & Pengaturan" subtitle="Kelola data diri, keamanan akun, dan preferensi notifikasi Anda." />

@if(isset($messages[$status]))
    <div class="mb-5 flex items-center gap-2 rounded border border-success/40 bg-success/10 px-4 py-3 text-sm text-success"><x-nav-icon name="check" :size="16" />{{ $messages[$status] }}</div>
@endif

<div x-data="{ tab: '{{ $startTab }}' }" class="grid lg:grid-cols-4 gap-6">
    <aside class="lg:col-span-1 space-y-4 min-w-0">
        <div class="panel p-5 text-center">
            <div class="flex justify-center mb-3"><x-avatar :user="$user" :size="80" /></div>
            <p class="font-heading font-semibold text-navy">{{ $user->name }}</p>
            <p class="text-xs text-muted break-all">{{ $user->email }}</p>
            <span class="badge text-brand mt-2">{{ $user->role === 'siswa' ? 'Siswa' : ($user->role === 'admin' ? 'Administrator' : 'Petugas TU') }}</span>
        </div>
        <nav class="panel p-2 flex lg:flex-col gap-1 overflow-x-auto text-sm" aria-label="Bagian pengaturan">
            @foreach(['profil' => ['Profil', 'users'], 'keamanan' => ['Keamanan', 'lock'], 'notifikasi' => ['Notifikasi', 'bell']] as $k => [$label, $icon])
                <button type="button" @click="tab='{{ $k }}'" :class="tab==='{{ $k }}' ? 'bg-navy text-white' : 'text-ink hover:bg-canvas'" class="flex items-center gap-2.5 px-3 py-2.5 rounded whitespace-nowrap">
                    <x-nav-icon :name="$icon" :size="16" />{{ $label }}
                </button>
            @endforeach
            @if($user->role === 'admin')
                <a href="{{ route('school-settings.edit') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded text-ink/70 hover:bg-canvas whitespace-nowrap"><x-nav-icon name="building" :size="16" />Pengaturan Sekolah</a>
            @endif
        </nav>
    </aside>

    <div class="lg:col-span-3 min-w-0">
        <section x-show="tab==='profil'" class="panel p-6 md:p-8">
            <h2 class="section-title mb-1">Data Diri</h2>
            <p class="text-sm text-muted mb-6">Foto dan kontak Anda membantu Tata Usaha mengenali dan menghubungi Anda.</p>
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6"
                  x-data="{
                      src: @js($user->avatarUrl() ?? ''),
                      original: @js($user->avatarUrl() ?? ''),
                      remove: false,
                      error: '',
                      pick(e) {
                          const f = e.target.files[0];
                          this.error = '';
                          if (!f) { this.src = this.original; return; }
                          if (!['image/jpeg', 'image/png', 'image/webp'].includes(f.type)) {
                              this.error = 'Format foto harus JPG, PNG, atau WebP.'; e.target.value = ''; return;
                          }
                          if (f.size > 2 * 1024 * 1024) {
                              this.error = 'Ukuran foto maksimal 2 MB. Mohon pilih foto yang lebih kecil.'; e.target.value = ''; return;
                          }
                          if (this.src && this.src.startsWith('blob:')) URL.revokeObjectURL(this.src);
                          this.src = URL.createObjectURL(f);
                          this.remove = false;
                      }
                  }">
                @csrf @method('PATCH')
                <div class="flex flex-wrap items-center gap-5">
                    <div class="w-20 h-20 rounded-full overflow-hidden bg-navy text-white flex items-center justify-center text-2xl font-bold">
                        <img x-show="src && !remove" :src="src" alt="Pratinjau foto profil" class="w-full h-full object-cover" x-on:error="src = ''">
                        <span x-show="!src || remove">{{ strtoupper(mb_substr($user->name, 0, 1)) }}</span>
                    </div>
                    <div class="space-y-2">
                        <label class="btn-secondary cursor-pointer">Pilih foto
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" class="sr-only" @change="pick($event)">
                        </label>
                        <label class="flex items-center gap-2 text-sm text-muted" x-show="src">
                            <input type="checkbox" name="remove_avatar" value="1" x-model="remove" class="rounded border-line text-brand"> Hapus foto saat ini
                        </label>
                        <p class="text-xs text-muted">JPG, PNG, atau WebP, maksimal 2 MB.</p>
                        <p x-show="error" x-cloak x-text="error" class="text-xs text-danger"></p>
                        @error('avatar')<p class="text-xs text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-5">
                    <div>
                        <label class="label">Nama lengkap</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="input">
                        @error('name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Email (untuk masuk)</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="input">
                        @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label">Nomor telepon</label>
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Contoh: 0812 3456 7890" class="input">
                        @error('phone')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                @if($student)
                    <div class="rounded border border-line bg-canvas p-4">
                        <p class="text-sm font-semibold text-navy mb-3">Data Akademik <span class="text-xs font-normal text-muted">(dikelola Tata Usaha)</span></p>
                        <dl class="grid sm:grid-cols-4 gap-4 text-sm">
                            <div><dt class="text-xs text-muted">NISN</dt><dd class="font-medium tnum">{{ $student->nisn }}</dd></div>
                            <div><dt class="text-xs text-muted">Kelas</dt><dd class="font-medium">{{ $student->class ?: '-' }}</dd></div>
                            <div><dt class="text-xs text-muted">Tahun masuk</dt><dd class="font-medium">{{ $student->entry_year ?: '-' }}</dd></div>
                            <div><dt class="text-xs text-muted">Status</dt><dd class="font-medium capitalize">{{ $student->status }}</dd></div>
                        </dl>
                        <p class="text-xs text-muted mt-3">Jika ada data yang keliru, silakan hubungi Tata Usaha.</p>
                    </div>
                @endif

                <button class="btn-primary">Simpan perubahan</button>
            </form>
        </section>

        <section x-show="tab==='keamanan'" x-cloak class="space-y-6">
            <div class="panel p-6 md:p-8">
                <h2 class="section-title mb-1">Ubah Kata Sandi</h2>
                <p class="text-sm text-muted mb-6">Gunakan kata sandi minimal 8 karakter yang tidak dipakai di tempat lain.</p>
                <form method="POST" action="{{ route('password.update') }}" class="space-y-5 max-w-md" x-data="{show:false}">
                    @csrf @method('PUT')
                    <div>
                        <div class="flex justify-between mb-1.5"><label class="block text-sm font-medium text-ink">Kata sandi saat ini</label>
                            <button type="button" @click="show=!show" class="text-xs text-brand" x-text="show ? 'Sembunyikan' : 'Tampilkan'"></button></div>
                        <input :type="show ? 'text' : 'password'" name="current_password" autocomplete="current-password" class="input">
                        @if($errors->updatePassword->has('current_password'))<p class="text-xs text-danger mt-1">{{ $errors->updatePassword->first('current_password') }}</p>@endif
                    </div>
                    <div>
                        <label class="label">Kata sandi baru</label>
                        <input :type="show ? 'text' : 'password'" name="password" autocomplete="new-password" class="input">
                        @if($errors->updatePassword->has('password'))<p class="text-xs text-danger mt-1">{{ $errors->updatePassword->first('password') }}</p>@endif
                    </div>
                    <div>
                        <label class="label">Ulangi kata sandi baru</label>
                        <input :type="show ? 'text' : 'password'" name="password_confirmation" autocomplete="new-password" class="input">
                    </div>
                    <button class="btn-primary">Ubah kata sandi</button>
                </form>
            </div>

            <div class="panel p-6 md:p-8">
                <h2 class="section-title mb-4">Aktivitas Akun</h2>
                <dl class="grid sm:grid-cols-3 gap-5 text-sm">
                    <div><dt class="text-xs text-muted">Masuk sebelumnya</dt><dd class="font-medium text-ink">{{ $user->previous_login_at ? $user->previous_login_at->translatedFormat('d F Y, H:i') : 'Belum ada catatan' }}</dd></div>
                    <div><dt class="text-xs text-muted">Masuk terakhir</dt><dd class="font-medium text-ink">{{ $user->last_login_at ? $user->last_login_at->translatedFormat('d F Y, H:i') : 'Belum ada catatan' }}</dd></div>
                    <div><dt class="text-xs text-muted">Akun dibuat</dt><dd class="font-medium text-ink">{{ $user->created_at->translatedFormat('d F Y') }}</dd></div>
                </dl>
                <p class="text-xs text-muted mt-4">Jika ada waktu masuk yang tidak Anda kenali, segera ubah kata sandi dan beri tahu Tata Usaha.</p>
            </div>
        </section>

        <section x-show="tab==='notifikasi'" x-cloak class="panel p-6 md:p-8">
            <h2 class="section-title mb-1">Preferensi Notifikasi</h2>
            <p class="text-sm text-muted mb-6">Pilih pemberitahuan yang ingin muncul di ikon lonceng.</p>
            <form method="POST" action="{{ route('profile.notifications') }}" class="space-y-3 max-w-lg">
                @csrf @method('PATCH')
                @foreach($notifOptions as $key => $label)
                    <label class="flex items-center justify-between gap-4 border border-line rounded px-4 py-3 cursor-pointer hover:bg-canvas">
                        <span class="text-sm text-ink">{{ $label }}</span>
                        <input type="checkbox" name="notif[{{ $key }}]" value="1" @checked($user->wantsNotification($key)) class="rounded border-line text-brand w-5 h-5">
                    </label>
                @endforeach
                <button class="btn-primary mt-3">Simpan preferensi</button>
            </form>
        </section>
    </div>
</div>
@endsection
