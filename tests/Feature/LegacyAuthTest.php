<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Customer;
use Database\Seeders\ConfigSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_config_returns_json_string(): void
    {
        $this->seed(ConfigSeeder::class);

        $response = $this->get('/config6789.php');

        $response->assertOk();
        $this->assertStringContainsString('text/html', (string) $response->headers->get('Content-Type'));
        $payload = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('API_SERVER', $payload);
        $this->assertArrayHasKey('APPUSERANDROID_VERSION', $payload);
        $this->assertArrayHasKey('APPTVBOX_VERSION', $payload);
        $this->assertArrayHasKey('APPADMINANDROID_VERSION', $payload);
    }

    public function test_swagger_try_it_out_gets_application_json(): void
    {
        $this->seed(ConfigSeeder::class);

        $response = $this->get('/config6789.php', [
            'Referer' => 'http://localhost:8000/api/documentation',
        ]);

        $response->assertOk();
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }

    public function test_customer_register_and_login(): void
    {
        $register = $this->post('/home/register', [
            'customer_name' => 'A',
            'phone_number' => '090',
            'email' => 'a@test.com',
            'password' => 'secret',
        ]);
        $register->assertOk();
        $body = json_decode($register->getContent(), true);
        $this->assertSame('1', $body['status']);
        $this->assertNotEmpty($body['id']);

        $login = $this->post('/home/login', [
            'email' => 'a@test.com',
            'password' => 'secret',
        ]);
        $info = json_decode($login->getContent(), true);
        $this->assertSame(1, $info['status']);
        $this->assertSame('a@test.com', $info['info'][0]['email']);
        $this->assertSame('secret', $info['info'][0]['password']);
        $this->assertNotEmpty($info['info'][0]['customer_token']);
        $this->assertNotEquals('secret', Customer::query()->first()->getRawOriginal('password'));
    }

    public function test_customer_login_with_phone_number(): void
    {
        $this->post('/home/register', [
            'customer_name' => 'A',
            'phone_number' => '0900000000',
            'email' => 'a@test.com',
            'password' => 'secret',
        ]);

        $login = $this->post('/home/login', [
            'email' => '0900000000',
            'password' => 'secret',
        ]);
        $info = json_decode($login->getContent(), true);
        $this->assertSame(1, $info['status']);
        $this->assertSame('0900000000', $info['info'][0]['phone_number']);
        $this->assertSame('a@test.com', $info['info'][0]['email']);
    }

    public function test_admin_login_with_md5_password(): void
    {
        Account::query()->create([
            'username' => 'admin',
            'password' => md5('admin123'),
            'email' => 'admin@test.com',
            'user_type' => '1',
            'deleted' => 'n',
        ]);

        $login = $this->post('/sysaccount/login', [
            'username' => 'admin',
            'password' => md5('admin123'),
        ]);
        $body = json_decode($login->getContent(), true);
        $this->assertSame(1, $body['status']);
        $this->assertSame('admin', $body['accountList'][0]['username']);
    }
}
