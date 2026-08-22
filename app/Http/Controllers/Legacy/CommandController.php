<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\DeviceCommand;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class CommandController extends Controller
{
    #[OA\Post(
        path: '/home/CreateCommand',
        summary: 'Phone/Admin tạo lệnh — lưu tb_commands. Laravel không gửi FCM.',
        tags: [AppTags::CUSTOMER, AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'sn', type: 'string'),
                new OA\Property(property: 'cmd_code', type: 'string'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'is_imme', type: 'string'),
                new OA\Property(property: 'second_wait', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{cmd_id}')]
    )]
    public function create(Request $request)
    {
        $sn = trim((string) $request->input('sn'));
        $cmdCode = trim((string) $request->input('cmd_code'));
        if ($sn === '' || $cmdCode === '') {
            return LegacyJson::send(['status' => -2, 'msg' => 'Thiếu sn hoặc cmd_code', 'cmd_id' => '']);
        }

        $now = now()->format('Y-m-d H:i:s');
        $wait = (int) $request->input('second_wait', 10);
        if ($wait < 1) {
            $wait = 10;
        }

        $row = DeviceCommand::query()->create([
            'sn' => $sn,
            'cmd_code' => $cmdCode,
            'content' => LegacyJson::str($request->input('content')),
            'is_imme' => LegacyJson::str($request->input('is_imme') === null || $request->input('is_imme') === '' ? '0' : $request->input('is_imme')),
            'second_wait' => $wait,
            'commit_time' => $now,
            'return_time' => $now,
            'return_value' => '',
            'sync' => '0',
            'done' => '0',
        ]);

        return LegacyJson::send([
            'status' => 1,
            'cmd_id' => LegacyJson::str($row->cmd_id),
            'msg' => LegacyJson::str($row->cmd_id),
        ]);
    }

    #[OA\Get(path: '/home/GetInfoCommand_ByID/{id}', summary: 'Phone chờ return_value — cmd_list', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'cmd_list')])]
    public function byId(string $id)
    {
        $row = DeviceCommand::query()->where('cmd_id', $id)->first();
        $list = $row ? [$row->toLegacyArray()] : [];

        return LegacyJson::send(['cmd_list' => $list]);
    }

    #[OA\Get(
        path: '/home/GetNewCommands_BySeriComputer/{serial}',
        summary: 'TV poll lệnh pending done=0 sync=0. Laravel không FCM.',
        tags: [AppTags::PROJECTOR],
        responses: [new OA\Response(response: 200, description: 'cmd_list')]
    )]
    public function newBySerial(string $serial)
    {
        $list = [];

        DB::transaction(function () use ($serial, &$list): void {
            $pending = DeviceCommand::query()
                ->where('sn', $serial)
                ->where('done', '0')
                ->where(function ($q): void {
                    $q->whereNull('sync')->orWhere('sync', '')->orWhere('sync', '0');
                })
                ->orderBy('cmd_id')
                ->lockForUpdate()
                ->get();

            $list = $pending->map(fn (DeviceCommand $c) => $c->toLegacyArray())->values()->all();

            if ($pending->isNotEmpty()) {
                DeviceCommand::query()
                    ->whereIn('cmd_id', $pending->pluck('cmd_id')->all())
                    ->update(['sync' => '1']);
            }
        });

        return LegacyJson::send(['cmd_list' => $list]);
    }

    #[OA\Post(
        path: '/home/ReplyCommand/{id}',
        summary: 'TV trả return_value, done=1',
        tags: [AppTags::PROJECTOR],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'return_value', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function reply(Request $request, string $id)
    {
        $row = DeviceCommand::query()->where('cmd_id', $id)->first();
        if (! $row) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy lệnh']);
        }

        $row->return_value = LegacyJson::str($request->input('return_value'));
        $row->return_time = now()->format('Y-m-d H:i:s');
        $row->done = '1';
        $row->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }
}
