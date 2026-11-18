<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Pengaturan sekolah dimuat sekali per permintaan
        $this->app->scoped('mas.settings', function () {
            try {
                return \App\Models\Setting::pluck('value', 'key')->all();
            } catch (\Throwable $e) {
                return []; // tabel belum dimigrasi
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bahasa Indonesia untuk pesan validasi dan format tanggal
        app()->setLocale('id');
        \Carbon\Carbon::setLocale('id');

        // Catat waktu masuk; simpan waktu masuk sebelumnya untuk ditampilkan di Profil
        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            $event->user->forceFill([
                'previous_login_at' => $event->user->last_login_at,
                'last_login_at' => now(),
            ])->saveQuietly();
        });
    }
}
