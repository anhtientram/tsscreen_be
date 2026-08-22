<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\AccountNotification;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NotifyController extends Controller
{
    #[OA\Get(path: '/home/GetNofity_ByIdCustomer/{customerId}', summary: 'List notify customer — Nofity_list', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byCustomer(string $customerId)
    {
        $list = CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->orderByDesc('id_notify')
            ->get()
            ->map(fn (CustomerNotification $n) => $n->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Nofity_list' => $list]);
    }

    #[OA\Get(path: '/home/GetNofityNew_ByIdCustomer/{customerId}', summary: 'Số notify chưa đọc (seen=0)', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: '{count}')])]
    public function newCountByCustomer(string $customerId)
    {
        $count = CustomerNotification::query()
            ->where('customer_id', $customerId)
            ->where('seen', '0')
            ->count();

        return LegacyJson::send(['count' => $count]);
    }

    #[OA\Get(path: '/home/GetNofity_ByIdAccount/{accountId}', summary: 'List notify admin — Nofity_list', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byAccount(string $accountId)
    {
        $list = AccountNotification::query()
            ->where('account_id', $accountId)
            ->orderByDesc('id_notify')
            ->get()
            ->map(fn (AccountNotification $n) => $n->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Nofity_list' => $list]);
    }

    #[OA\Get(path: '/home/GetNofityNew_ByIdAccount/{accountId}', summary: 'Số notify admin chưa đọc', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{count}')])]
    public function newCountByAccount(string $accountId)
    {
        $count = AccountNotification::query()
            ->where('account_id', $accountId)
            ->where('seen', '0')
            ->count();

        return LegacyJson::send(['count' => $count]);
    }

    #[OA\Get(path: '/home/GetNofity_ById/{id}', summary: 'Một notify (customer hoặc admin)', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'Nofity_list')])]
    public function byId(string $id)
    {
        $row = CustomerNotification::query()->where('id_notify', $id)->first();
        if ($row) {
            return LegacyJson::send(['Nofity_list' => [$row->toLegacyArray()]]);
        }

        $admin = AccountNotification::query()->where('id_notify', $id)->first();
        $list = $admin ? [$admin->toLegacyArray()] : [];

        return LegacyJson::send(['Nofity_list' => $list]);
    }

    #[OA\Get(path: '/home/UpdateNotify/{id}', summary: 'Đánh dấu đã đọc seen=1', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'status 1')])]
    public function markRead(string $id)
    {
        CustomerNotification::query()->where('id_notify', $id)->update(['seen' => '1']);
        AccountNotification::query()->where('id_notify', $id)->update(['seen' => '1']);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/home/InsertNotify',
        summary: 'Tạo notify customer (Phone/TV/Admin duyệt gói). Không FCM. Admin FormData customer_id = customer thật (field Dart tên accountId).',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR, AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'customer_id', type: 'string'),
                new OA\Property(property: 'account_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'descript', type: 'string'),
                new OA\Property(property: 'detail', type: 'string'),
                new OA\Property(property: 'picture', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function insert(Request $request)
    {
        $payload = $this->payload($request);
        $accountId = trim((string) $request->input('account_id'));
        $customerId = trim((string) $request->input('customer_id'));

        if ($accountId !== '' && Account::query()->where('account_id', $accountId)->exists()) {
            $row = $this->storeAccount((int) $accountId, $payload);

            return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($row->id_notify)]);
        }

        if ($customerId !== '' && Customer::query()->where('customer_id', $customerId)->exists()) {
            $row = $this->storeCustomer((int) $customerId, $payload);

            return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($row->id_notify)]);
        }

        if ($customerId !== '' && Account::query()->where('account_id', $customerId)->exists()) {
            $row = $this->storeAccount((int) $customerId, $payload);

            return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($row->id_notify)]);
        }

        return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy người nhận']);
    }

    #[OA\Post(
        path: '/home/InsertNotify_Account',
        summary: 'Phone gửi notify tới từng admin (account_id). Không FCM.',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'account_id', type: 'string'),
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'descript', type: 'string'),
                new OA\Property(property: 'detail', type: 'string'),
                new OA\Property(property: 'picture', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function insertAccount(Request $request)
    {
        $accountId = trim((string) $request->input('account_id'));
        if ($accountId === '' || ! Account::query()->where('account_id', $accountId)->exists()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy tài khoản']);
        }

        $row = $this->storeAccount((int) $accountId, $this->payload($request));

        return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($row->id_notify)]);
    }

    private function payload(Request $request): array
    {
        return [
            'title' => LegacyJson::str($request->input('title')),
            'descript' => LegacyJson::str($request->input('descript')),
            'detail' => LegacyJson::str($request->input('detail')),
            'picture' => LegacyJson::str($request->input('picture')),
            'seen' => '0',
        ];
    }

    private function storeCustomer(int $customerId, array $payload): CustomerNotification
    {
        return CustomerNotification::query()->create($payload + ['customer_id' => $customerId]);
    }

    private function storeAccount(int $accountId, array $payload): AccountNotification
    {
        return AccountNotification::query()->create($payload + ['account_id' => $accountId]);
    }
}
