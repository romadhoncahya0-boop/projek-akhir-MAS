@csrf
<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="block text-sm font-medium text-ink mb-1.5">Nama lengkap</label>
        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required class="w-full rounded-lg border-line text-sm">
        @error('name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-ink mb-1.5">Email (untuk masuk)</label>
        <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required class="w-full rounded-lg border-line text-sm">
        @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>
    <div x-data="{ role: '{{ old('role', $user->role ?? 'petugas') }}' }" class="sm:col-span-2 grid sm:grid-cols-2 gap-5">
        <div>
            <label class="block text-sm font-medium text-ink mb-1.5">Peran</label>
            <select name="role" x-model="role" class="w-full rounded-lg border-line text-sm">
                @foreach($roles as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
            </select>
            @error('role')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>
        <div x-show="role === 'siswa'" x-cloak>
            <label class="block text-sm font-medium text-ink mb-1.5">Hubungkan ke data siswa</label>
            <select name="student_id" class="w-full rounded-lg border-line text-sm">
                <option value="">Pilih siswa (opsional)</option>
                @foreach($students as $s)
                    <option value="{{ $s->id }}" @selected(old('student_id', optional($user)->student?->id) == $s->id)>{{ $s->name }} ({{ $s->nisn }})</option>
                @endforeach
            </select>
            <p class="text-xs text-muted mt-1">Wajib dihubungkan agar siswa bisa membuka portalnya.</p>
        </div>
    </div>
    <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-ink mb-1.5">Kata sandi {{ isset($user) ? '(kosongkan jika tidak diubah)' : '' }}</label>
        <input type="password" name="password" {{ isset($user) ? '' : 'required' }} minlength="8" autocomplete="new-password" class="w-full sm:w-1/2 rounded-lg border-line text-sm">
        <p class="text-xs text-muted mt-1">Minimal 8 karakter.</p>
        @error('password')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>
</div>
<div class="flex gap-3 mt-6">
    <button class="btn-primary">Simpan</button>
    <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
</div>
