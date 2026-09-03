<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Packet;
use App\Services\PacketQuota;
use Carbon\Carbon;
use Database\Seeders\AuthSeeder;
use Database\Seeders\PacketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyPacketTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));
        $this->seed([AuthSeeder::class, PacketSeeder::class]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_catalog_and_admin_create_packet(): void
    {
        $list = $this->get('/home/GetAllPacket');
        $list->assertOk();
        $body = json_decode($list->getContent(), true);
        $this->assertArrayHasKey('Packet_list', $body);
        $this->assertNotEmpty($body['Packet_list']);
        $this->assertIsString($body['Packet_list'][0]['packet_id']);
        $this->assertIsString($body['Packet_list'][0]['detail']);
        $this->assertSame('99.000', collect($body['Packet_list'])->firstWhere('name_packet', 'Gói cơ bản')['price']);

        $create = $this->post('/home/CreatePacket', [
            'name_packet' => 'Gói test',
            'price' => '1000',
            'price_6_month' => '5000',
            'price_12_month' => '9000',
            'month_qty' => '1',
            'day_qty' => '0',
            'year_qty' => '0',
            'picture' => '',
            'detail' => '2 TV',
            'description' => 'Test',
            'limit_qty' => '2',
            'limit_capacity' => '1048576',
            'account_id' => '1',
            'is_trial' => '0',
        ]);
        $created = json_decode($create->getContent(), true);
        $this->assertSame(1, $created['status']);

        $id = $created['msg'];
        $update = $this->post('/home/UpdatePacket_ById/'.$id, [
            'name_packet' => 'Gói test 2',
            'price' => '2000',
            'limit_qty' => '3',
            'limit_capacity' => '2048',
            'detail' => '3 TV',
            'description' => 'Test 2',
            'is_trial' => '0',
        ]);
        $this->assertSame(1, json_decode($update->getContent(), true)['status']);

        $this->delete('/home/DeletePacket_ById/'.$id);
        $this->assertSame('y', Packet::query()->where('packet_id', $id)->value('deleted'));
    }

    public function test_buy_activate_quota_and_vietqr(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $packet = Packet::query()->where('name_packet', 'Gói cơ bản')->first();

        $buy = $this->post('/home/BuyPacket_ByIdCustomer_1', [
            'packet_id' => $packet->packet_id,
            'name_packet' => $packet->name_packet,
            'price' => $packet->price,
            'description' => $packet->description,
            'detail' => $packet->detail,
            'customer_id' => $customer->customer_id,
            'is_trial' => '0',
            'pay_month' => '1',
            'is_business' => '0',
        ]);
        $bought = json_decode($buy->getContent(), true);
        $this->assertSame(1, $bought['status']);
        $paidId = $bought['msg'];

        $new = json_decode($this->get('/sysaccount/OrderNew')->getContent(), true);
        $this->assertNotEmpty($new['orderList']);
        $this->assertSame('0', $new['orderList'][0]['pay']);
        $this->assertSame($customer->phone_number, $new['orderList'][0]['phone_number']);

        $this->assertFalse(PacketQuota::canAddDevice($customer->customer_id));

        $activate = $this->post('/sysaccount/active_order_1/'.$paidId, [
            'vaild_date' => '2026-08-20',
            'packet_id' => $packet->packet_id,
            'payment_date' => '2026-08-20',
        ]);
        $this->assertSame(1, json_decode($activate->getContent(), true)['status']);

        $order = Order::query()->where('paid_id', $paidId)->first();
        $this->assertSame('1', $order->pay);
        $this->assertSame('2026-08-20', $order->valid_date);
        $this->assertSame('2026-09-20', $order->expire_date);

        $this->assertTrue(PacketQuota::canAddDevice($customer->customer_id));
        $this->assertSame(2, PacketQuota::limitQty($customer->customer_id));

        $mine = json_decode($this->get('/home/GetPacket_ByCustomerId/'.$customer->customer_id)->getContent(), true);
        $this->assertSame($paidId, $mine['Packet_list'][0]['paid_id']);
        $this->assertIsString($mine['Packet_list'][0]['limit_qty']);

        $tx = $this->post('/home/Get_Transactions_ByCustomerId', ['customer_id' => $customer->customer_id]);
        $this->assertStringContainsString('application/json', (string) $tx->headers->get('Content-Type'));
        $txBody = json_decode($tx->getContent(), true);
        $this->assertNotEmpty($txBody['transaction_list']);

        $qr = $this->post('/vietQR/getQRCode_ByPaidId', ['paid_id' => $paidId]);
        $this->assertStringContainsString('application/json', (string) $qr->headers->get('Content-Type'));
        $this->assertNotEmpty(json_decode($qr->getContent(), true)['qrLink']);

        $customers = json_decode($this->get('/home/GetListCustomer')->getContent(), true);
        $this->assertArrayHasKey('list', $customers);
        $this->assertIsArray($customers['list'][0]['devices']);
    }

    public function test_buy_trial_auto_activates_with_dates(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $packet = Packet::query()->where('name_packet', 'Gói dùng thử')->first();

        $buy = $this->post('/home/BuyPacket_ByIdCustomer_1', [
            'packet_id' => $packet->packet_id,
            'name_packet' => $packet->name_packet,
            'price' => $packet->price,
            'description' => $packet->description,
            'detail' => $packet->detail,
            'customer_id' => $customer->customer_id,
            'is_trial' => '1',
            'is_business' => '0',
        ]);
        $paidId = json_decode($buy->getContent(), true)['msg'];

        $order = Order::query()->where('paid_id', $paidId)->first();
        $this->assertSame('1', $order->pay);
        $this->assertSame('2026-08-20', $order->register_date);
        $this->assertSame('2026-08-20', $order->payment_date);
        $this->assertSame('2026-08-20', $order->valid_date);
        $this->assertSame('2026-08-27', $order->expire_date);

        $mine = json_decode($this->get('/home/GetPacket_ByCustomerId/'.$customer->customer_id)->getContent(), true);
        $trial = collect($mine['Packet_list'])->firstWhere('paid_id', $paidId);
        $this->assertSame('2026-08-20', $trial['payment_date']);
        $this->assertSame('2026-08-20', $trial['valid_date']);
        $this->assertSame('2026-08-27', $trial['expire_date']);

        $this->assertTrue(PacketQuota::canAddDevice($customer->customer_id));

        $new = json_decode($this->get('/sysaccount/OrderNew')->getContent(), true);
        $this->assertEmpty(collect($new['orderList'])->where('paid_id', $paidId));
    }

    public function test_buy_six_month_uses_price_6_month(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $packet = Packet::query()->where('name_packet', 'Gói cơ bản')->first();

        $buy = $this->post('/home/BuyPacket_ByIdCustomer_1', [
            'packet_id' => $packet->packet_id,
            'name_packet' => $packet->name_packet,
            'price' => $packet->price,
            'customer_id' => $customer->customer_id,
            'pay_month' => '6',
            'detail' => $packet->detail,
        ]);
        $paidId = json_decode($buy->getContent(), true)['msg'];
        $this->assertSame('499000', Order::query()->where('paid_id', $paidId)->value('price'));
    }

    public function test_admin_limit_capacity_one_is_one_gigabyte(): void
    {
        $this->assertSame(1024 * 1024 * 1024, PacketQuota::bytesFromLimit('1'));
        $this->assertSame(10 * 1024 * 1024 * 1024, PacketQuota::bytesFromLimit('10'));
        $this->assertSame(1048576, PacketQuota::bytesFromLimit('1048576'));

        $create = $this->post('/home/CreatePacket', [
            'name_packet' => 'Gói 1GB',
            'price' => '1',
            'detail' => '1GB',
            'description' => '1GB',
            'limit_qty' => '1',
            'limit_capacity' => '1',
            'is_trial' => '0',
        ]);
        $id = json_decode($create->getContent(), true)['msg'];
        $this->assertSame((string) (1024 * 1024 * 1024), Packet::query()->where('packet_id', $id)->value('limit_capacity'));
    }
}
