<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Device;
use App\Models\Packet;
use Carbon\Carbon;
use Database\Seeders\AuthSeeder;
use Database\Seeders\PacketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyDirDeviceTest extends TestCase
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

    public function test_create_dir_pair_tv_and_quota(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();

        $dirRes = $this->post('/home/CreateDir', [
            'name_dir' => 'Tầng 1',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'group',
        ]);
        $dirBody = json_decode($dirRes->getContent(), true);
        $this->assertSame(1, $dirBody['status']);
        $this->assertIsString($dirBody['msg']);
        $idDir = $dirBody['msg'];

        $mine = json_decode($this->get('/home/GetDirCustomer_ById/'.$customer->customer_id)->getContent(), true);
        $this->assertArrayHasKey('Dir_list', $mine);
        $this->assertSame($idDir, $mine['Dir_list'][0]['id_dir']);
        $this->assertIsString($mine['Dir_list'][0]['created_by']);
        $this->assertTrue(ctype_digit($mine['Dir_list'][0]['created_by']));

        $noPacket = $this->post('/home/CreateDevice', [
            'computer_name' => 'TV 1',
            'seri_computer' => 'SERIAL001',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
            'provinces' => '',
            'district' => '',
            'wards' => '',
            'location' => '',
            'time_end' => '',
        ]);
        $this->assertSame(-2, json_decode($noPacket->getContent(), true)['status']);

        $this->activateBasic($customer);

        $ok = $this->post('/home/CreateDevice', [
            'computer_name' => 'TV 1',
            'seri_computer' => 'SERIAL001',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
            'provinces' => '',
            'district' => '',
            'wards' => '',
            'location' => '',
            'time_end' => '',
        ]);
        $this->assertSame(1, json_decode($ok->getContent(), true)['status']);

        $devices = json_decode($this->get('/home/GetDevices_ByCustomerId/'.$customer->customer_id)->getContent(), true);
        $this->assertCount(1, $devices['Device_list']);
        $this->assertSame('SERIAL001', $devices['Device_list'][0]['seri_computer']);
        $this->assertIsString($devices['Device_list'][0]['computer_id']);
        $this->assertSame('0', $devices['Device_list'][0]['turn_on']);
        $this->assertSame('0', $devices['Device_list'][0]['isCheckOnProjector']);

        $inDir = json_decode($this->get('/home/GetDevice_ByIdDir/'.$idDir)->getContent(), true);
        $this->assertCount(1, $inDir['Device_list']);

        $external = json_decode($this->get('/home/GetDevicesNotBelongAnyDir_ByCustomerId/'.$customer->customer_id)->getContent(), true);
        $this->assertSame([], $external['Device_list']);

        $computerId = $devices['Device_list'][0]['computer_id'];
        $token = $this->get('/home/UpdateComputerToken_ById/'.$computerId.'/fcm:token/value');
        $this->assertSame(1, json_decode($token->getContent(), true)['status']);
        $this->assertSame('fcm:token/value', Device::query()->first()->computer_token);

        $this->post('/home/UpdateRomMemory/'.$computerId, [
            'rom_memory_total' => '32',
            'rom_memory_used' => '10',
        ]);
        $this->get('/home/UpdateAliveTimeDevice_ById/'.$computerId);

        $this->post('/home/CreateDevice', [
            'computer_name' => 'TV 2',
            'seri_computer' => 'SERIAL002',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $this->post('/home/CreateDevice', [
            'computer_name' => 'TV 3',
            'seri_computer' => 'SERIAL003',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $over = $this->post('/home/CreateDevice', [
            'computer_name' => 'TV 3',
            'seri_computer' => 'SERIAL003',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $this->assertSame(-2, json_decode($over->getContent(), true)['status']);
        $this->assertSame(2, Device::query()->where('deleted', 'n')->count());
    }

    public function test_share_dir_and_on_off(): void
    {
        $owner = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $other = Customer::query()->create([
            'customer_name' => 'B',
            'email' => 'b@test.com',
            'phone_number' => '091',
            'password' => 'x',
            'status' => 'y',
            'deleted' => 'n',
        ]);

        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Share',
            'customer_id' => $owner->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $share = $this->post('/home/InsertDirShare', [
            'id_dir' => $idDir,
            'customer_idfrom' => $owner->customer_id,
            'customer_idto' => $other->customer_id,
            'checkOwner' => '1',
        ]);
        $this->assertSame(1, json_decode($share->getContent(), true)['status']);

        $shared = json_decode($this->get('/home/GetDirCustomer_SharedById/'.$other->customer_id)->getContent(), true);
        $this->assertSame('1', $shared['Dir_list'][0]['is_owner']);

        $users = json_decode($this->get('/home/GetSharedCustomerList_ByDirID/'.$idDir)->getContent(), true);
        $this->assertSame(1, $users['status']);
        $this->assertNotEmpty($users['userList']);

        $this->post('/home/UpDateOnOffDeviceDir_ById/'.$idDir, [
            'turnon_time' => '07:00',
            'turnoff_time' => '22:00',
            'customer_id' => $owner->customer_id,
        ]);
        $one = json_decode($this->get('/home/GetDir_ById/'.$idDir)->getContent(), true);
        $this->assertSame('07:00', $one['Dir_list'][0]['turnon_time']);
    }

    private function activateBasic(Customer $customer): void
    {
        $packet = Packet::query()->where('name_packet', 'Gói cơ bản')->first();
        $buy = $this->post('/home/BuyPacket_ByIdCustomer_1', [
            'packet_id' => $packet->packet_id,
            'name_packet' => $packet->name_packet,
            'price' => $packet->price,
            'customer_id' => $customer->customer_id,
            'pay_month' => '1',
            'detail' => $packet->detail,
        ]);
        $paidId = json_decode($buy->getContent(), true)['msg'];
        $this->post('/sysaccount/active_order_1/'.$paidId, [
            'vaild_date' => '2026-08-20',
            'packet_id' => $packet->packet_id,
            'payment_date' => '2026-08-20',
        ]);
    }
}
