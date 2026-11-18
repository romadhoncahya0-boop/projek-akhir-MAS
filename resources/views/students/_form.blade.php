@csrf
@if(isset($student)) @method('PUT') @endif

<div class="grid sm:grid-cols-2 gap-5">
    <div>
        <label class="label" for="nisn">NISN</label>
        <input id="nisn" type="text" name="nisn" value="{{ old('nisn', $student->nisn ?? '') }}" required maxlength="15" inputmode="numeric" class="input">
        @error('nisn')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="name">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name', $student->name ?? '') }}" required class="input">
        @error('name')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="class">Kelas</label>
        <input id="class" type="text" name="class" value="{{ old('class', $student->class ?? '') }}" placeholder="cth: XII RPL 1" class="input">
        @error('class')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="entry_year">Tahun Masuk</label>
        <input id="entry_year" type="text" name="entry_year" value="{{ old('entry_year', $student->entry_year ?? '') }}" placeholder="2023/2024" class="input">
        @error('entry_year')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="phone">No. Telepon</label>
        <input id="phone" type="tel" name="phone" value="{{ old('phone', $student->phone ?? '') }}" class="input">
        @error('phone')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $student->email ?? '') }}" class="input">
        @error('email')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="status">Status</label>
        <select id="status" name="status" required class="input">
            @foreach(['aktif' => 'Aktif', 'lulus' => 'Lulus', 'pindah' => 'Pindah'] as $value => $label)
                <option value="{{ $value }}" @selected(old('status', $student->status ?? 'aktif') == $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('status')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>
</div>

@if(!isset($student))
    <div class="mt-5 border border-line rounded-lg p-4 bg-canvas">
        <label class="flex items-start gap-3 text-sm text-ink">
            <input type="checkbox" name="create_login" value="1" @checked(old('create_login')) class="mt-0.5 rounded border-line text-brand">
            <span>
                Buatkan akun login Portal Akademik untuk siswa ini.
                <span class="block text-xs text-muted mt-0.5">Email login otomatis dibuat dari NISN (format: [nisn]@siswa.mas.sch.id), kata sandi awal: <code>password</code>.</span>
            </span>
        </label>
    </div>
@elseif($student->user_id)
    <p class="mt-5 text-xs text-success bg-success/10 rounded-lg px-3 py-2">Siswa ini sudah memiliki akun login Portal Akademik ({{ $student->user->email }}).</p>
@else
    <div class="mt-5 border border-line rounded-lg p-4 bg-canvas">
        <label class="flex items-start gap-3 text-sm text-ink">
            <input type="checkbox" name="create_login" value="1" class="mt-0.5 rounded border-line text-brand">
            <span>
                Buatkan akun login Portal Akademik untuk siswa ini sekarang.
                <span class="block text-xs text-muted mt-0.5">Kata sandi awal: <code>password</code>.</span>
            </span>
        </label>
    </div>
@endif

<div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-8 pt-5 border-t border-line">
    <a href="{{ route('students.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($student) ? 'Simpan Perubahan' : 'Simpan Data Siswa' }}</button>
</div>
