<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Packet;
use App\Models\ResourceFile;
use Carbon\Carbon;
use Database\Seeders\AuthSeeder;
use Database\Seeders\PacketSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyMediaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::parse('2026-08-20 12:00:00'));
        $this->seed([AuthSeeder::class, PacketSeeder::class]);
        Storage::fake('uploads');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dir_upload_list_chunk_and_quota(): void
    {
        $customer = Customer::query()->where('email', 'customer@tsscreen.local')->first();
        $token = $customer->customer_token;

        $missing = json_decode($this->post('/home/checkdir_customer', ['name_dir' => $token])->getContent(), true);
        $this->assertSame(0, $missing['status']);

        $this->post('/home/createdir_customer', ['name_dir' => $token]);
        $exists = json_decode($this->post('/home/checkdir_customer', ['name_dir' => $token])->getContent(), true);
        $this->assertSame(1, $exists['status']);

        $noPacket = $this->post('/home/uploadfile_customer', [
            'name_dir' => $token,
            'customer_id' => $customer->customer_id,
            'fileupload' => UploadedFile::fake()->create('clip.mp4', 20, 'video/mp4'),
        ]);
        $this->assertNotSame(1, json_decode($noPacket->getContent(), true)['status']);

        $this->activateBasic($customer);

        $upload = $this->post('/home/uploadfile_customer', [
            'name_dir' => $token,
            'customer_id' => $customer->customer_id,
            'fileupload' => UploadedFile::fake()->create('clip.mp4', 20, 'video/mp4'),
        ]);
        $body = json_decode($upload->getContent(), true);
        $this->assertSame(1, $body['status']);
        $this->assertSame('./uploads/'.$token.'/clip.mp4', $body['path_file']['path']);
        $this->assertIsInt($body['path_file']['file_size']);

        $this->call('HEAD', '/uploads/'.$token.'/clip.mp4')->assertOk();
        $this->get('/uploads/'.$token.'/clip.mp4')->assertOk();

        $files = json_decode($this->post('/home/getfiles_customer', ['name_dir' => $token])->getContent(), true);
        $this->assertCount(1, $files['file_list']);

        $size = json_decode($this->post('/home/getsizeofdir_customer', ['name_dir' => $token])->getContent(), true);
        $this->assertIsString($size['totalsize']);
        $this->assertGreaterThan(0, (int) $size['totalsize']);

        $c1 = $this->post('/home/uploadfile_customer_large', [
            'name_dir' => $token,
            'customer_id' => $customer->customer_id,
            'filename' => 'big.mp4',
            'chunk_index' => 1,
            'total_chunks' => 2,
            'fileupload' => UploadedFile::fake()->create('big.mp4', 10, 'video/mp4'),
        ]);
        $this->assertSame(1, json_decode($c1->getContent(), true)['status']);
        $this->assertArrayNotHasKey('path_file', json_decode($c1->getContent(), true));

        $c2 = $this->post('/home/uploadfile_customer_large', [
            'name_dir' => $token,
            'customer_id' => $customer->customer_id,
            'filename' => 'big.mp4',
            'chunk_index' => 2,
            'total_chunks' => 2,
            'fileupload' => UploadedFile::fake()->create('big.mp4', 10, 'video/mp4'),
        ]);
        $assembled = json_decode($c2->getContent(), true);
        $this->assertSame(1, $assembled['status']);
        $this->assertSame('./uploads/'.$token.'/big.mp4', $assembled['path_file']['path']);

        ResourceFile::query()->create([
            'customer_id' => $customer->customer_id,
            'name_dir' => $token,
            'name' => 'fill.bin',
            'path' => './uploads/'.$token.'/fill.bin',
            'file_size' => 1024 * 1024 * 1024,
            'file_type' => 'video/mp4',
            'creation_time' => now(),
            'deleted' => 'n',
        ]);

        $over = $this->post('/home/uploadfile_customer', [
            'name_dir' => $token,
            'customer_id' => $customer->customer_id,
            'fileupload' => UploadedFile::fake()->create('more.mp4', 20, 'video/mp4'),
        ]);
        $this->assertNotSame(1, json_decode($over->getContent(), true)['status']);

        $this->post('/home/deletefile_customer', [
            'name_dir' => $token,
            'name_file' => 'clip.mp4',
        ]);
        $after = json_decode($this->post('/home/getfiles_customer', ['name_dir' => $token])->getContent(), true);
        $names = array_column($after['file_list'], 'name');
        $this->assertNotContains('clip.mp4', $names);
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
