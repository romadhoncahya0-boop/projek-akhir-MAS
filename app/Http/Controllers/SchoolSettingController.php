<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

/** Pengaturan sekolah (khusus admin): kontak, jam layanan, SLA, pengumuman. */
class SchoolSettingController extends Controller
{
    public function edit()
    {
        $values = [];
        foreach (array_keys(Setting::DEFAULTS) as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('school-settings.edit', ['values' => $values]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'school_name' => ['required', 'string', 'max:100'],
            'school_address' => ['required', 'string', 'max:200'],
            'contact_email' => ['required', 'email', 'max:150'],
            'contact_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'service_days' => ['required', 'string', 'max:60'],
            'service_hours' => ['required', 'string', 'max:60'],
            'sla_days' => ['required', 'integer', 'min:1', 'max:14'],
            'announcement' => ['nullable', 'string', 'max:500'],
        ], [
            'contact_phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, tanda + atau -.',
            'sla_days.min' => 'Estimasi layanan minimal 1 hari kerja.',
            'sla_days.max' => 'Estimasi layanan maksimal 14 hari kerja.',
        ]);

        Setting::setMany(array_map(fn ($v) => $v ?? '', $data));

        return redirect()->route('school-settings.edit')->with('success', 'Pengaturan sekolah berhasil disimpan.');
    }
}
