<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Packet;
use Carbon\Carbon;
use Database\Seeders\AuthSeeder;
use Database\Seeders\PacketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyCampaignTest extends TestCase
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

    public function test_create_approve_and_tv_today_schedule(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $this->activateBasic($customer);

        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Hall',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $this->post('/home/CreateDevice', [
            'computer_name' => 'Box',
            'seri_computer' => 'TVBOX1',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $computerId = Device::query()->value('computer_id');

        $create = $this->post('/home/CreateCamp', [
            'campaign_name' => 'QC sáng',
            'status' => '1',
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
            'from_time' => '08:00:00',
            'to_time' => '18:00:00',
            'days_of_week' => 'T5',
            'video_type' => 'url',
            'url_youtobe' => 'https://example.com/a.mp4',
            'url_yotobe' => 'https://example.com/a.mp4',
            'url_usp' => '',
            'customer_id' => $customer->customer_id,
            'id_dir' => $idDir,
            'computer_id' => '',
            'video_duration' => '30',
        ]);
        $created = json_decode($create->getContent(), true);
        $this->assertSame(1, $created['status']);
        $campId = $created['msg'];

        $this->post('/home/AddTimeRun_ByCamp', [
            'campaign_id' => $campId,
            'from_time' => '08:00:00',
            'to_time' => '12:00:00',
        ]);

        $phoneList = json_decode($this->get('/home/GetAllCamp_ById/'.$customer->customer_id)->getContent(), true);
        $this->assertArrayHasKey('camp_list', $phoneList);
        $this->assertSame('url', $phoneList['camp_list'][0]['video_type']);
        $this->assertSame('https://example.com/a.mp4', $phoneList['camp_list'][0]['url_youtobe']);
        $this->assertSame('0', $phoneList['camp_list'][0]['default_campaign_id']);

        $todayBefore = json_decode($this->get('/home/GetCampToday_ByComputerId/'.$computerId.'/2026-08-20/1')->getContent(), true);
        $this->assertSame([], $todayBefore['Camp_list']);

        $this->post('/home/ApproveCamp_ById/'.$campId, [
            'approved_yn' => '1',
            'customer_id' => $customer->customer_id,
        ]);

        $today = json_decode($this->get('/home/GetCampToday_ByComputerId/'.$computerId.'/2026-08-20/1')->getContent(), true);
        $this->assertCount(1, $today['Camp_list']);
        $this->assertSame($campId, $today['Camp_list'][0]['campaign_id']);

        $times = json_decode($this->get('/home/GetTimeRun_ByCampId/'.$campId)->getContent(), true);
        $this->assertArrayHasKey('camp_list_time', $times);
        $this->assertSame('08:00:00', $times['camp_list_time'][0]['from_time']);

        $sched = json_decode($this->post('/home/GetAllRunTimeOfComputer_4', [
            'computer_id' => $computerId,
            'work_date' => '2026-08-20',
        ])->getContent(), true);
        $this->assertCount(1, $sched['Camp_list']);
        $this->assertSame('08:00:00', $sched['Camp_list'][0]['from_time']);
        $this->assertArrayHasKey('url_youtobe', $sched['Camp_list'][0]);

        $this->post('/home/AddCampaignRunProfile', [
            'customer_id' => $customer->customer_id,
            'customer_name' => $customer->customer_name,
            'campaign_id' => $campId,
            'campaign_name' => 'QC sáng',
            'url' => 'https://example.com/a.mp4',
            'computer_id' => $customer->customer_id,
            'seri_computer' => 'TVBOX1',
            'run_time' => '2026-08-20 08:00:00',
            'computer_name' => 'Box',
        ]);

        $profiles = json_decode($this->post('/home/GetCampaignRunProfile', [
            'campaign_id' => $campId,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
        ])->getContent(), true);
        $this->assertNotEmpty($profiles['Profile_list']);
        $this->assertSame((string) $computerId, $profiles['Profile_list'][0]['computer_id']);

        $general = json_decode($this->post('/home/GetCampaignRunProfile_Genaral', [
            'customer_id' => $customer->customer_id,
            'from_date' => '2026-08-01',
            'to_date' => '2026-08-31',
        ])->getContent(), true);
        $this->assertSame('1', $general['Profile_list'][0]['run_total']);

        $this->assertSame('1', Campaign::query()->first()->approved_yn);
    }

    public function test_create_camp_empty_flags_become_zero(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Hall',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $create = $this->post('/home/CreateCamp', [
            'campaign_id' => '',
            'campaign_name' => 'image_picker_demo.jpg',
            'status' => '1',
            'video_id' => '',
            'from_date' => '',
            'to_date' => '',
            'from_time' => '',
            'to_time' => '',
            'days_of_week' => '',
            'video_type' => 'url',
            'url_youtobe' => 'https://example.com/a.jpg',
            'url_yotobe' => 'https://example.com/a.jpg',
            'url_usp' => '',
            'customer_id' => $customer->customer_id,
            'computer_id' => '',
            'id_dir' => $idDir,
            'id_computer' => '',
            'video_duration' => '15',
            'approved_yn' => '1',
            'default_yn' => '',
            'run_by_default_yn' => '',
            'accept_count' => '',
            'accept_customers' => '',
            'is_owner' => '',
            'name_dir' => '',
            'isNew' => '',
        ]);
        $body = json_decode($create->getContent(), true);
        $this->assertSame(1, $body['status']);
        $camp = Campaign::query()->first();
        $this->assertSame('0', $camp->default_yn);
        $this->assertSame('0', $camp->run_by_default_yn);
        $this->assertSame('1', $camp->approved_yn);
        $this->assertNull($camp->computer_id);
    }

    public function test_create_camp_id_computer_is_only_for_that_device(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $this->activateBasic($customer);

        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Hall',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $this->post('/home/CreateDevice', [
            'computer_name' => 'TV A',
            'seri_computer' => 'TVA',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $this->post('/home/CreateDevice', [
            'computer_name' => 'TV B',
            'seri_computer' => 'TVB',
            'status' => '1',
            'center_id' => '5',
            'customer_id' => $customer->customer_id,
            'type' => 'chủ sở hữu',
            'id_dir' => $idDir,
        ]);
        $tvA = Device::query()->where('seri_computer', 'TVA')->value('computer_id');
        $tvB = Device::query()->where('seri_computer', 'TVB')->value('computer_id');

        $create = $this->post('/home/CreateCamp', [
            'campaign_name' => 'Chi TV A',
            'status' => '1',
            'video_type' => 'url',
            'url_youtobe' => 'https://example.com/a.mp4',
            'customer_id' => $customer->customer_id,
            'id_dir' => $idDir,
            'computer_id' => '',
            'id_computer' => $tvA,
            'approved_yn' => '1',
            'video_duration' => '10',
        ]);
        $body = json_decode($create->getContent(), true);
        $this->assertSame(1, $body['status']);
        $camp = Campaign::query()->where('campaign_id', $body['msg'])->first();
        $this->assertSame((string) $tvA, (string) $camp->computer_id);
        $this->assertSame((string) $tvA, (string) $camp->id_computer);

        $onA = json_decode($this->get('/home/Getcamp_ByComputerId/'.$tvA.'/1')->getContent(), true);
        $onB = json_decode($this->get('/home/Getcamp_ByComputerId/'.$tvB.'/1')->getContent(), true);
        $this->assertCount(1, $onA['Camp_list']);
        $this->assertSame((string) $tvA, $onA['Camp_list'][0]['id_computer']);
        $this->assertSame([], $onB['Camp_list']);

        $todayA = json_decode($this->get('/home/GetCampToday_ByComputerId/'.$tvA.'/2026-08-20/1')->getContent(), true);
        $todayB = json_decode($this->get('/home/GetCampToday_ByComputerId/'.$tvB.'/2026-08-20/1')->getContent(), true);
        $this->assertCount(1, $todayA['Camp_list']);
        $this->assertSame([], $todayB['Camp_list']);
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

    public function test_create_camp_without_customer_id_uses_id_dir(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $this->activateBasic($customer);

        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Lobby',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $create = $this->post('/home/CreateCamp', [
            'campaign_name' => 'QC tối',
            'status' => '1',
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-30',
            'days_of_week' => 'T2,T3,T4,T5,T6,T7,CN',
            'video_type' => 'url',
            'url_youtobe' => 'https://example.com/night.mp4',
            'customer_id' => '',
            'id_dir' => $idDir,
            'id_computer' => '0',
            'approved_yn' => '1',
            'default_yn' => '1',
        ]);

        $created = json_decode($create->getContent(), true);
        $this->assertSame(1, $created['status']);

        $camp = Campaign::query()->find($created['msg']);
        $this->assertNotNull($camp);
        $this->assertSame((string) $customer->customer_id, (string) $camp->customer_id);
        $this->assertSame('1', $camp->default_yn);

        $byDir = json_decode($this->get('/home/Getcamp_ByDirId/'.$idDir.'/all')->getContent(), true);
        $this->assertSame('1', $byDir['Camp_list'][0]['default_yn']);

        $this->post('/home/AddTimeRun_ByCamp', [
            'campaign_id' => $created['msg'],
            'from_time' => '08:00:00',
            'to_time' => '18:00:00',
        ]);

        $defaultTimes = json_decode($this->get('/home/GetDefaultTimeRun_ByIdDir/'.$idDir)->getContent(), true);
        $this->assertCount(1, $defaultTimes['camp_list_time']);
    }

    public function test_multiple_default_camps_can_coexist_in_same_dir(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $this->activateBasic($customer);

        $idDir = json_decode($this->post('/home/CreateDir', [
            'name_dir' => 'Lobby',
            'customer_id' => $customer->customer_id,
            'type_dir' => 'g',
        ])->getContent(), true)['msg'];

        $firstId = json_decode($this->post('/home/CreateCamp', [
            'status' => '1',
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-30',
            'days_of_week' => 'T2,T3,T4,T5,T6',
            'video_type' => 'url',
            'customer_id' => $customer->customer_id,
            'id_dir' => $idDir,
            'approved_yn' => '1',
            'default_yn' => '1',
        ])->getContent(), true)['msg'];

        $secondId = json_decode($this->post('/home/CreateCamp', [
            'status' => '1',
            'from_date' => '2026-09-01',
            'to_date' => '2026-09-30',
            'days_of_week' => 'T7,CN',
            'video_type' => 'url',
            'customer_id' => $customer->customer_id,
            'id_dir' => $idDir,
            'approved_yn' => '1',
            'default_yn' => '1',
        ])->getContent(), true)['msg'];

        $this->get('/home/UpdateDefaultCamp_ById/'.$secondId)->assertOk();

        $this->assertSame('1', Campaign::query()->find($firstId)->default_yn);
        $this->assertSame('1', Campaign::query()->find($secondId)->default_yn);

        $byDir = json_decode($this->get('/home/Getcamp_ByDirId/'.$idDir.'/all')->getContent(), true);
        $this->assertCount(2, $byDir['Camp_list']);
        $this->assertSame(['1', '1'], array_column($byDir['Camp_list'], 'default_yn'));
    }
}
