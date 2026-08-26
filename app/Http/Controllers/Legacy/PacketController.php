<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Packet;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class PacketController extends Controller
{
    #[OA\Get(path: '/home/GetAllPacket', summary: 'Catalog gói', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{Packet_list}')])]
    public function index()
    {
        $list = Packet::query()
            ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
            ->orderBy('packet_id')
            ->get()
            ->map(fn (Packet $p) => $p->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Packet_list' => $list]);
    }

    #[OA\Post(
        path: '/home/CreatePacket',
        summary: 'Admin tạo gói (thêm is_trial, giá 1/6/12 tháng, limit_qty, limit_capacity bytes)',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'name_packet', type: 'string'),
                new OA\Property(property: 'price', type: 'string'),
                new OA\Property(property: 'price_6_month', type: 'string'),
                new OA\Property(property: 'price_12_month', type: 'string'),
                new OA\Property(property: 'month_qty', type: 'string'),
                new OA\Property(property: 'day_qty', type: 'string'),
                new OA\Property(property: 'year_qty', type: 'string'),
                new OA\Property(property: 'picture', type: 'string'),
                new OA\Property(property: 'detail', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'limit_qty', type: 'string', description: 'Số TV tối đa'),
                new OA\Property(property: 'limit_capacity', type: 'string', description: 'Bytes; 1–1024 = GB'),
                new OA\Property(property: 'account_id', type: 'string'),
                new OA\Property(property: 'is_trial', type: 'string'),
                new OA\Property(property: 'is_business', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{status:1}')]
    )]
    public function store(Request $request)
    {
        $packet = Packet::fillFromRequest($request);
        $packet->save();

        return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($packet->packet_id)]);
    }

    #[OA\Post(
        path: '/home/UpdatePacket_ById/{id}',
        summary: 'Admin sửa gói',
        tags: [AppTags::ADMIN],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'name_packet', type: 'string'),
                new OA\Property(property: 'price', type: 'string'),
                new OA\Property(property: 'price_6_month', type: 'string'),
                new OA\Property(property: 'price_12_month', type: 'string'),
                new OA\Property(property: 'limit_qty', type: 'string'),
                new OA\Property(property: 'limit_capacity', type: 'string'),
                new OA\Property(property: 'is_trial', type: 'string'),
                new OA\Property(property: 'is_business', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{status:1}')]
    )]
    public function update(Request $request, string $id)
    {
        $packet = Packet::query()->where('packet_id', $id)->first();
        if (! $packet) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Không tìm thấy gói']);
        }

        Packet::fillFromRequest($request, $packet)->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Delete(
        path: '/home/DeletePacket_ById/{id}',
        summary: 'Admin xóa gói (soft delete)',
        tags: [AppTags::ADMIN],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: '{status:1}')]
    )]
    public function destroy(string $id)
    {
        $packet = Packet::query()->where('packet_id', $id)->first();
        if ($packet) {
            $packet->deleted = 'y';
            $packet->save();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }
}
