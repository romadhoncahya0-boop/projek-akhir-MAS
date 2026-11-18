@csrf
@if(isset($document)) @method('PUT') @endif

<div class="grid sm:grid-cols-2 gap-5">
    @if(!isset($document))
        <div>
            <label class="label" for="nisn">NISN Siswa</label>
            <input id="nisn" type="text" name="nisn" value="{{ old('nisn') }}" required inputmode="numeric" placeholder="Masukkan NISN siswa" class="input">
            @error('nisn')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
        </div>
    @endif

    <div>
        <label class="label" for="document_type_id">Jenis Dokumen</label>
        <select id="document_type_id" name="document_type_id" required class="input">
            <option value="">Pilih jenis dokumen</option>
            @foreach($documentTypes as $type)
                <option value="{{ $type->id }}" @selected(old('document_type_id', $document->document_type_id ?? '') == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        @error('document_type_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="document_number">Nomor Dokumen</label>
        <input id="document_number" type="text" name="document_number" value="{{ old('document_number', $document->document_number ?? '') }}" class="input">
        @error('document_number')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="issue_date">Tanggal Terbit</label>
        <input id="issue_date" type="date" name="issue_date" value="{{ old('issue_date', optional($document->issue_date ?? null)->format('Y-m-d')) }}" class="input">
        @error('issue_date')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="academic_year">Tahun Ajaran</label>
        <input id="academic_year" type="text" name="academic_year" value="{{ old('academic_year', $document->academic_year ?? '') }}" placeholder="2025/2026" maxlength="9" class="input">
        @error('academic_year')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="label" for="file">
            File Dokumen <span class="text-xs text-muted font-normal">(PDF, JPG, atau PNG, maks. 2 MB){{ isset($document) ? ' — kosongkan jika tidak diganti' : '' }}</span>
        </label>
        <input id="file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" {{ isset($document) ? '' : 'required' }}
               class="w-full text-sm border-2 border-dashed border-line rounded-lg p-3 bg-canvas file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-softblue file:text-brand">
        @error('file')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="label" for="notes">Keterangan Tambahan</label>
        <textarea id="notes" name="notes" rows="3" class="input">{{ old('notes', $document->notes ?? '') }}</textarea>
        @error('notes')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>
</div>

<div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 mt-8 pt-5 border-t border-line">
    <a href="{{ route('documents.index') }}" class="btn-secondary">Batal</a>
    <button type="submit" class="btn-primary">{{ isset($document) ? 'Simpan Perubahan' : 'Simpan & Kirim untuk Verifikasi' }}</button>
</div>
