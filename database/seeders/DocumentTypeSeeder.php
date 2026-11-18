<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Ijazah', 'code' => 'IJZ', 'category' => 'Berkas Identitas'],
            ['name' => 'Rapor Hasil Belajar', 'code' => 'RPR', 'category' => 'Rapor & Transkrip'],
            ['name' => 'Transkrip Nilai Akademik', 'code' => 'TRK', 'category' => 'Rapor & Transkrip'],
            ['name' => 'SKHUN', 'code' => 'SKH', 'category' => 'Surat Keterangan'],
            ['name' => 'Surat Keterangan Siswa Aktif', 'code' => 'SKA', 'category' => 'Surat Keterangan'],
            ['name' => 'Surat Keterangan Berkelakuan Baik', 'code' => 'SKBB', 'category' => 'Surat Keterangan'],
            ['name' => 'Surat Keterangan Rekomendasi SNBP', 'code' => 'SNBP', 'category' => 'Surat Keterangan'],
            ['name' => 'Legalisir Ijazah & Rapor', 'code' => 'LGL', 'category' => 'Berkas Identitas'],
            ['name' => 'Sertifikat Kejuruan', 'code' => 'SRT', 'category' => 'Sertifikat & Piagam'],
            ['name' => 'Piagam Prestasi / Olimpiade', 'code' => 'PGM', 'category' => 'Sertifikat & Piagam'],
        ];

        foreach ($types as $type) {
            DocumentType::create($type);
        }
    }
}
