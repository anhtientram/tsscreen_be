<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountNotification;
use App\Models\Customer;
use App\Models\CustomerNotification;
use Database\Seeders\AuthSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyNotifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_list_count_read_and_insert(): void
    {
        $this->seed(AuthSeeder::class);
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();

        $empty = json_decode($this->get('/home/GetNofity_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertSame([], $empty['Nofity_list']);

        $zero = json_decode($this->get('/home/GetNofityNew_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertSame(0, $zero['count']);

        $insert = $this->post('/home/InsertNotify', [
            'customer_id' => $customer->customer_id,
            'title' => 'Chia sẻ',
            'descript' => 'Bạn nhận được chia sẻ hệ thống mới',
            'detail' => 'Chi tiết',
            'picture' => '',
        ]);
        $created = json_decode($insert->getContent(), true);
        $this->assertSame(1, $created['status']);
        $this->assertNotEmpty($created['msg']);

        $list = json_decode($this->get('/home/GetNofity_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertCount(1, $list['Nofity_list']);
        $this->assertIsString($list['Nofity_list'][0]['id_notify']);
        $this->assertSame('Bạn nhận được chia sẻ hệ thống mới', $list['Nofity_list'][0]['descript']);
        $this->assertSame('0', $list['Nofity_list'][0]['seen']);

        $count = json_decode($this->get('/home/GetNofityNew_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertSame(1, $count['count']);

        $id = $list['Nofity_list'][0]['id_notify'];
        $one = json_decode($this->get('/home/GetNofity_ById/'.$id)->getContent(), true);
        $this->assertCount(1, $one['Nofity_list']);
        $this->assertSame($id, $one['Nofity_list'][0]['id_notify']);

        $this->get('/home/UpdateNotify/'.$id);
        $this->assertSame('1', CustomerNotification::query()->where('id_notify', $id)->value('seen'));
        $after = json_decode($this->get('/home/GetNofityNew_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertSame(0, $after['count']);
    }

    public function test_admin_insert_notify_goes_to_customer_and_account_inbox(): void
    {
        $this->seed(AuthSeeder::class);
        $admin = Account::query()->where('username', 'admin')->first();
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();

        // Admin active gói: FormData customer_id = order.customerId (field Dart tên accountId)
        $this->post('/home/InsertNotify', [
            'customer_id' => $customer->customer_id,
            'title' => 'Gói cước',
            'descript' => 'Gói cước đã được duyệt thành công',
            'detail' => 'Nạp lại thiết bị',
            'picture' => '',
        ]);
        $forCustomer = json_decode($this->get('/home/GetNofity_ByIdCustomer/'.$customer->customer_id)->getContent(), true);
        $this->assertCount(1, $forCustomer['Nofity_list']);
        $this->assertSame('Gói cước đã được duyệt thành công', $forCustomer['Nofity_list'][0]['descript']);

        $this->post('/home/InsertNotify_Account', [
            'account_id' => $admin->account_id,
            'title' => 'Đơn mới',
            'descript' => 'Có đơn hàng mới',
            'detail' => 'Có đơn hàng mới',
            'picture' => '',
        ]);
        $list = json_decode($this->get('/home/GetNofity_ByIdAccount/'.$admin->account_id)->getContent(), true);
        $this->assertCount(1, $list['Nofity_list']);
        $this->assertIsString($list['Nofity_list'][0]['account_id']);
        $this->assertSame((string) $admin->account_id, $list['Nofity_list'][0]['account_id']);

        $count = json_decode($this->get('/home/GetNofityNew_ByIdAccount/'.$admin->account_id)->getContent(), true);
        $this->assertSame(1, $count['count']);
    }

    public function test_insert_notify_account_and_tv_customer_insert(): void
    {
        $this->seed(AuthSeeder::class);
        $admin = Account::query()->where('username', 'admin')->first();
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();

        $toAdmin = json_decode($this->post('/home/InsertNotify_Account', [
            'account_id' => $admin->account_id,
            'title' => 'Chi tiết đơn',
            'descript' => 'desc',
            'detail' => 'Chi tiết đơn',
            'picture' => '',
        ])->getContent(), true);
        $this->assertSame(1, $toAdmin['status']);
        $this->assertSame(1, AccountNotification::query()->where('account_id', $admin->account_id)->count());

        $tv = json_decode($this->post('/home/InsertNotify', [
            'customer_id' => $customer->customer_id,
            'title' => 'TV',
            'descript' => 't',
            'detail' => 'd',
            'picture' => '',
        ])->getContent(), true);
        $this->assertSame(1, $tv['status']);

        $missing = json_decode($this->post('/home/InsertNotify', [
            'customer_id' => '999999',
            'title' => 'x',
        ])->getContent(), true);
        $this->assertSame(-2, $missing['status']);
    }

    public function test_swagger_referer_returns_json(): void
    {
        $this->seed(AuthSeeder::class);
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();

        $response = $this->get('/home/GetNofityNew_ByIdCustomer/'.$customer->customer_id, [
            'Referer' => 'http://localhost:8000/api/documentation',
        ]);
        $response->assertOk();
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }
}
