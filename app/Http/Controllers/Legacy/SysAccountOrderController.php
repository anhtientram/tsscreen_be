<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\OpenApi\AppTags;
use App\Services\OrderActivationService;
use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SysAccountOrderController extends Controller
{
    public function __construct(private readonly OrderActivationService $activation) {}
    #[OA\Get(path: '/sysaccount/OrderNew', summary: 'Đơn chờ kích hoạt (pay=0)', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{orderList}')])]
    public function newOrders()
    {
        return $this->orderListResponse(
            Order::query()->with('customer')->where('pay', '0')->where(fn (Builder $q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
        );
    }

    #[OA\Get(path: '/sysaccount/GetAllOrder', summary: 'Tất cả đơn', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{orderList}')])]
    public function allOrders()
    {
        return $this->orderListResponse(Order::query()->with('customer'));
    }

    #[OA\Get(path: '/sysaccount/order_endtime', summary: 'Đơn đã hết hạn', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{orderList}')])]
    public function expiredOrders()
    {
        $today = now()->format('Y-m-d');

        return $this->orderListResponse(
            Order::query()
                ->with('customer')
                ->where('pay', '1')
                ->where(fn (Builder $q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
                ->whereNotNull('expire_date')
                ->where('expire_date', '!=', '')
                ->where('expire_date', '<', $today)
        );
    }

    #[OA\Get(
        path: '/sysaccount/detail_order/{paidId}',
        summary: 'Chi tiết đơn',
        tags: [AppTags::ADMIN],
        parameters: [new OA\Parameter(name: 'paidId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: '{orderList}')]
    )]
    public function detail(string $paidId)
    {
        $order = Order::query()->with('customer')->where('paid_id', $paidId)->first();

        return LegacyJson::send([
            'orderList' => $order ? [$order->toAdminArray()] : [],
        ]);
    }

    #[OA\Post(
        path: '/sysaccount/Filter_Packet',
        summary: 'Lọc đơn (thống kê). Ngày yyyy-MM-dd',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'name_packet', type: 'string'),
                new OA\Property(property: 'sdt', type: 'string'),
                new OA\Property(property: 'reg1', type: 'string'),
                new OA\Property(property: 'reg2', type: 'string'),
                new OA\Property(property: 'validate1', type: 'string'),
                new OA\Property(property: 'validate2', type: 'string'),
                new OA\Property(property: 'exdate1', type: 'string'),
                new OA\Property(property: 'exdate2', type: 'string'),
                new OA\Property(property: 'pay1', type: 'string'),
                new OA\Property(property: 'pay2', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{orderList}')]
    )]
    public function filter(Request $request)
    {
        $query = Order::query()->with('customer');

        if ($name = $this->filled($request, 'name_packet')) {
            $query->where('name_packet', 'like', '%'.$name.'%');
        }

        if ($phone = $this->filled($request, 'sdt')) {
            $query->whereHas('customer', fn (Builder $q) => $q->where('phone_number', 'like', '%'.$phone.'%'));
        }

        $this->applyDateRange($query, 'register_date', $request, 'reg1', 'reg2');
        $this->applyDateRange($query, 'valid_date', $request, 'validate1', 'validate2');
        $this->applyDateRange($query, 'expire_date', $request, 'exdate1', 'exdate2');
        $this->applyDateRange($query, 'payment_date', $request, 'pay1', 'pay2');

        return $this->orderListResponse($query);
    }

    #[OA\Post(
        path: '/sysaccount/active_order_1/{paidId}',
        summary: 'Admin kích hoạt đơn. Field vaild_date là typo của app.',
        tags: [AppTags::ADMIN],
        parameters: [new OA\Parameter(name: 'paidId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'vaild_date', type: 'string', description: 'yyyy-MM-dd (typo app)'),
                new OA\Property(property: 'packet_id', type: 'string'),
                new OA\Property(property: 'payment_date', type: 'string', description: 'yyyy-MM-dd'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1 hoặc -2')]
    )]
    public function activate(Request $request, string $paidId)
    {
        $order = Order::query()->with('packet')->where('paid_id', $paidId)->first();
        if (! $order) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy đơn hàng']);
        }

        $validDate = $this->filled($request, 'vaild_date')
            ?: $this->filled($request, 'valid_date')
            ?: now()->format('Y-m-d');
        $paymentDate = $this->filled($request, 'payment_date') ?: now()->format('Y-m-d');
        $packetId = $this->filled($request, 'packet_id');

        $this->activation->activate($order, $validDate, $paymentDate, $packetId);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    private function orderListResponse(Builder $query)
    {
        $list = $query->orderByDesc('paid_id')
            ->get()
            ->map(fn (Order $o) => $o->toAdminArray())
            ->values()
            ->all();

        return LegacyJson::send(['orderList' => $list]);
    }

    private function applyDateRange(Builder $query, string $column, Request $request, string $fromKey, string $toKey): void
    {
        if ($from = $this->filled($request, $fromKey)) {
            $query->where($column, '>=', $from);
        }

        if ($to = $this->filled($request, $toKey)) {
            $query->where($column, '<=', $to.' 23:59:59');
        }
    }

    private function filled(Request $request, string $key): ?string
    {
        $value = trim((string) $request->input($key, ''));

        if ($value === '' || $value === 'null' || str_contains($value, '0000-00-00') || str_contains($value, '00/00/0000')) {
            return null;
        }

        return $value;
    }
}
