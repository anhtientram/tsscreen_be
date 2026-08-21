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
