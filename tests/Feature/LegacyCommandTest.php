<?php

namespace Tests\Feature;

use App\Models\DeviceCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_get_info_tv_poll_and_reply(): void
    {
        $empty = json_decode($this->get('/home/GetNewCommands_BySeriComputer/SERIAL001')->getContent(), true);
        $this->assertSame([], $empty['cmd_list']);

        $create = json_decode($this->post('/home/CreateCommand', [
            'sn' => 'SERIAL001',
            'cmd_code' => 'VIDEO_PAUSE',
            'content' => '',
            'is_imme' => '0',
            'second_wait' => '10',
        ])->getContent(), true);
        $this->assertSame(1, $create['status']);
        $this->assertIsString($create['cmd_id']);
        $this->assertNotEmpty($create['cmd_id']);

        $info = json_decode($this->get('/home/GetInfoCommand_ByID/'.$create['cmd_id'])->getContent(), true);
        $this->assertCount(1, $info['cmd_list']);
        $row = $info['cmd_list'][0];
        $this->assertSame($create['cmd_id'], $row['cmd_id']);
        $this->assertSame('VIDEO_PAUSE', $row['cmd_code']);
        $this->assertSame('SERIAL001', $row['sn']);
        $this->assertSame('0', $row['done']);
        $this->assertSame('0', $row['is_imme']);
        $this->assertSame('10', $row['second_wait']);
        $this->assertSame('', $row['return_value']);
        $this->assertNotEmpty($row['commit_time']);
        $this->assertNotEmpty($row['return_time']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $row['commit_time']);

        $poll = json_decode($this->get('/home/GetNewCommands_BySeriComputer/SERIAL001')->getContent(), true);
        $this->assertCount(1, $poll['cmd_list']);
        $this->assertSame('VIDEO_PAUSE', $poll['cmd_list'][0]['cmd_code']);
        $this->assertIsString($poll['cmd_list'][0]['cmd_id']);
        $this->assertIsString($poll['cmd_list'][0]['second_wait']);

        $again = json_decode($this->get('/home/GetNewCommands_BySeriComputer/SERIAL001')->getContent(), true);
        $this->assertSame([], $again['cmd_list']);

        $reply = json_decode($this->post('/home/ReplyCommand/'.$create['cmd_id'], [
            'return_value' => 'PAUSE_VIDEO',
        ])->getContent(), true);
        $this->assertSame(1, $reply['status']);

        $after = json_decode($this->get('/home/GetInfoCommand_ByID/'.$create['cmd_id'])->getContent(), true);
        $this->assertSame('PAUSE_VIDEO', $after['cmd_list'][0]['return_value']);
        $this->assertSame('1', $after['cmd_list'][0]['done']);
        $this->assertSame('1', DeviceCommand::query()->where('cmd_id', $create['cmd_id'])->value('done'));
    }

    public function test_create_rejects_missing_sn_and_other_serial_empty(): void
    {
        $missing = json_decode($this->post('/home/CreateCommand', [
            'cmd_code' => 'GET_TIMENOW',
        ])->getContent(), true);
        $this->assertSame(-2, $missing['status']);

        $this->post('/home/CreateCommand', [
            'sn' => 'TV-A',
            'cmd_code' => 'GET_TIMENOW',
            'content' => '',
            'is_imme' => '0',
            'second_wait' => '10',
        ]);
        $other = json_decode($this->get('/home/GetNewCommands_BySeriComputer/TV-B')->getContent(), true);
        $this->assertSame([], $other['cmd_list']);
    }

    public function test_swagger_referer_returns_json(): void
    {
        $response = $this->get('/home/GetNewCommands_BySeriComputer/SERIAL001', [
            'Referer' => 'http://localhost:8000/api/documentation',
        ]);
        $response->assertOk();
        $this->assertStringContainsString('application/json', (string) $response->headers->get('Content-Type'));
    }
}
