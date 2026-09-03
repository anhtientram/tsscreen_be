<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AppConfig;
use App\Services\TvBoxApkStorage;
use Database\Seeders\AuthSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AuthSeeder::class);
    }

    public function test_admin_login_page_loads(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_admin_can_login_and_view_orders(): void
    {
        $account = Account::query()->where('username', 'admin')->first();

        $this->actingAs($account, 'admin')
            ->get('/admin/orders')
            ->assertOk();
    }

    public function test_admin_config_page_loads_and_saves(): void
    {
        $account = Account::query()->where('username', 'admin')->first();

        $this->actingAs($account, 'admin')
            ->get('/admin/config')
            ->assertOk();

        Livewire::actingAs($account, 'admin')
            ->test(\App\Filament\Pages\ManageAppConfig::class)
            ->fillForm([
                'COMPANY_NAME' => 'TS Screen QA',
                'API_SERVER' => 'https://api.example.com',
                'ACTIVE_FLAG' => '1',
                'show_payment' => true,
            ])
            ->call('save')
            ->assertNotified();

        $map = AppConfig::map();
        $this->assertSame('TS Screen QA', $map['COMPANY_NAME']);
    }

    public function test_admin_can_upload_tvbox_apk_via_post(): void
    {
        $account = Account::query()->where('username', 'admin')->first();

        AppConfig::putMany(['API_SERVER' => 'https://api.example.com']);

        $this->actingAs($account, 'admin')
            ->post(route('admin.apk.upload'), [
                'apk' => UploadedFile::fake()->create('tvbox.apk', 512, 'application/vnd.android.package-archive'),
            ])
            ->assertRedirect('/admin/config');

        $this->assertTrue(TvBoxApkStorage::exists());
        $this->assertSame('https://api.example.com/apk/tvbox.apk', AppConfig::map()['APPTVBOX_UPDATE_URL']);
    }

    public function test_admin_config_page_loads_when_apk_already_hosted(): void
    {
        TvBoxApkStorage::ensureDirectory();
        file_put_contents(TvBoxApkStorage::absolutePath(), str_repeat('x', 1024));

        $account = Account::query()->where('username', 'admin')->first();

        $this->actingAs($account, 'admin')
            ->get('/admin/config')
            ->assertOk()
            ->assertSee('tvbox.apk');

        @unlink(TvBoxApkStorage::absolutePath());
    }
}
