<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_emoji_and_new_name_on_all_pages(): void
    {
        $this->seed();
        $emoji = '/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{2B50}\x{FF0B}]/u';
        $pages = ['guest' => ['/', '/login'], 'admin' => ['/dashboard', '/permohonan', '/documents', '/students', '/reports'], 'siswa' => ['/dashboard', '/dokumen-saya', '/ajukan-dokumen', '/riwayat-pengajuan']];
        foreach ($pages as $who => $urls) {
            foreach ($urls as $url) {
                if ($who !== 'guest') {
                    $this->actingAs(User::where('role', $who)->first());
                }
                $html = $this->get($url)->assertOk()->getContent();
                $this->assertSame(0, preg_match($emoji, $html), "Emoji ditemukan di $who $url");
                $this->assertStringNotContainsString('SMDAS', $html, "Nama lama di $who $url");
                $this->assertStringContainsString('MAS', $html);
            }
            $this->app['auth']->logout();
        }
    }

    public function test_friendly_error_pages(): void
    {
        $this->get('/halaman-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
    }

    public function test_indonesian_validation_message(): void
    {
        $this->post('/login', ['email' => 'x', 'password' => ''])->assertSessionHasErrors();
        $this->assertStringContainsString('email', session('errors')->first('email'));
    }
}
