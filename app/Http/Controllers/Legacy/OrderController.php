<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Packet;
use App\Models\Transaction;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class OrderController extends Controller
{
    #[OA\Post(
        path: '/home/BuyPacket_ByIdCustomer_1',
        summary: 'Phone mua/gia hạn gói — tạo đơn pay=0 chờ admin kích hoạt',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'packet_id', type: 'string'),
                new OA\Property(property: 'name_packet', type: 'string'),
                new OA\Property(property: 'price', type: 'string'),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'detail', type: 'string'),
                new OA\Property(property: 'customer_id', type: 'string'),
                new OA\Property(property: 'is_trial', type: 'string'),
                new OA\Property(property: 'pay_month', type: 'string', description: '1|6|12'),
                new OA\Property(property: 'is_business', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1 hoặc -2')]
    )]
    public function buy(Request $request)
    {
        $customer = Customer::query()->where('customer_id', $request->input('customer_id'))->first();
        $packet = Packet::query()->where('packet_id', $request->input('packet_id'))->first();

        if (! $customer || ! $customer->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách hàng']);
        }

        if (! $packet || $packet->deleted === 'y') {
            return LegacyJson::send(['status' => -2, 'msg' => 'Gói cước không tồn tại']);
        }

        $payMonth = $request->input('pay_month');
        $price = LegacyJson::parseMoney($request->input('price') ?: $packet->price);
        if ((int) $payMonth === 6) {
            $price = LegacyJson::parseMoney($packet->price_6_month ?: $price);
        }
        if ((int) $payMonth === 12) {
            $price = LegacyJson::parseMoney($packet->price_12_month ?: $price);
        }

        $isRenew = Order::query()
            ->where('customer_id', $customer->customer_id)
            ->where('pay', '1')
            ->exists();

        $isTrial = ($request->input('is_trial', $packet->is_trial ?: '0') === '1');
        $registerDate = now()->format('Y-m-d');

        $order = Order::query()->create([
            'packet_id' => $packet->packet_id,
            'customer_id' => $customer->customer_id,
            'packet_code' => 'PK'.$packet->packet_id,
            'name_packet' => $request->input('name_packet', $packet->name_packet),
            'price' => $price,
            'price_6_month' => $packet->price_6_month,
            'price_12_month' => $packet->price_12_month,
            'day_qty' => $packet->day_qty,
            'month_qty' => $packet->month_qty,
            'year_qty' => $packet->year_qty,
            'pay_month' => $payMonth,
            'is_trial' => $isTrial ? '1' : '0',
            'is_business' => $request->input('is_business', $packet->is_business ?: '0'),
            'detail' => $request->input('detail', $packet->detail),
            'description' => $request->input('description', $packet->description),
            'picture' => $packet->picture,
            'pay' => '0',
            'type' => $isRenew ? 'renew' : 'new',
            'register_date' => $registerDate,
            'limit_capacity' => $packet->limit_capacity,
            'limit_qty' => $packet->limit_qty,
            'deleted' => 'n',
        ]);

        $order->reg_number = 'DH'.$order->paid_id;

        if ($isTrial) {
            $order->pay = '1';
            $order->payment_date = $registerDate;
            $order->valid_date = $registerDate;
            $order->expire_date = substr($order->computeExpireDate($registerDate, $packet), 0, 10);
        }

        $order->save();

        if ($isTrial) {
            Transaction::query()->create([
                'paid_id' => $order->paid_id,
                'packet_id' => $order->packet_id,
                'customer_id' => $order->customer_id,
                'reg_number' => $order->reg_number,
                'name_packet' => $order->name_packet,
                'amount' => $order->price,
                'payment_date' => $registerDate,
                'ref_transaction_id' => '',
                'created_date' => now(),
            ]);
        }

        return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($order->paid_id)]);
    }

    #[OA\Get(
        path: '/home/GetPacket_ByCustomerId/{customerId}',
        summary: 'Gói đã mua — phone + TV (TV chỉ cần có gói active)',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR],
        parameters: [new OA\Parameter(name: 'customerId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: '{Packet_list}')]
    )]
    public function byCustomer(string $customerId)
    {
        $list = Order::query()
            ->with('packet')
            ->where('customer_id', $customerId)
            ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
            ->orderByDesc('paid_id')
            ->get()
            ->map(fn (Order $o) => $o->toMyPacketArray())
            ->values()
            ->all();

        return LegacyJson::send(['Packet_list' => $list]);
    }

    #[OA\Get(
        path: '/home/CancelPacket_ById/{paidId}',
        summary: 'Hủy đơn/gói',
        tags: [AppTags::CUSTOMER],
        parameters: [new OA\Parameter(name: 'paidId', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'status 1 hoặc -2')]
    )]
    public function cancel(string $paidId)
    {
        $order = Order::query()->where('paid_id', $paidId)->first();
        if (! $order) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy đơn hàng']);
        }

        $order->deleted = 'y';
        $order->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/home/Get_Transactions_ByCustomerId',
        summary: 'Lịch sử giao dịch (phone đọc response.data Map, luôn JSON)',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'customer_id', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{transaction_list}')]
    )]
    public function transactions(Request $request)
    {
        $list = Transaction::query()
            ->where('customer_id', $request->input('customer_id'))
            ->orderByDesc('transaction_id')
            ->get()
            ->map(fn (Transaction $t) => [
                'transaction_id' => LegacyJson::str($t->transaction_id),
                'paid_id' => LegacyJson::str($t->paid_id),
                'reg_number' => LegacyJson::str($t->reg_number),
                'packet_id' => LegacyJson::str($t->packet_id),
                'customer_id' => LegacyJson::str($t->customer_id),
                'payment_date' => LegacyJson::str($t->payment_date),
                'amount' => LegacyJson::money($t->amount),
                'ref_transaction_id' => LegacyJson::str($t->ref_transaction_id),
                'name_packet' => LegacyJson::str($t->name_packet),
            ])
            ->values()
            ->all();

        return LegacyJson::send(['transaction_list' => $list], forceJson: true);
    }
}
