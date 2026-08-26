<?php

namespace Tests\Feature;

use App\Support\AppLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppLogViewerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $dir = AppLog::directory();
        if (is_dir($dir)) {
            foreach (glob($dir.'/app-*.log') ?: [] as $file) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    public function test_app_log_writes_json_and_redacts_secrets(): void
    {
        AppLog::error('Hết dung lượng gói', [
            'password' => 'secret',
            'reason' => 'quota',
        ]);

        $path = AppLog::todayPath();
        $this->assertFileExists($path);
        $line = trim((string) file_get_contents($path));
        $row = json_decode($line, true);
        $this->assertSame('error', $row['level']);
        $this->assertSame('Hết dung lượng gói', $row['msg']);
        $this->assertSame('*', $row['ctx']['password']);
        $this->assertSame('quota', $row['ctx']['reason']);
    }

    public function test_logs_app_requires_key_and_renders_page(): void
    {
        $this->get('/logs/app')->assertForbidden();
        $this->get('/logs/app?key=wrong-key-xx')->assertForbidden();

        AppLog::error('Ổ đĩa hosting gần đầy', ['bytes' => 1]);

        $ok = $this->get('/logs/app?key=test-log-key-tsscreen');
        $ok->assertOk();
        $ok->assertSee('Logs ứng dụng', false);
        $ok->assertSee('Ổ đĩa hosting gần đầy', false);
        $ok->assertSee('storage/logs/app', false);
    }
}
