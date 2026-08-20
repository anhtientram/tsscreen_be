<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Device;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CustomerAdminController extends Controller
{
    #[OA\Get(path: '/home/GetListCustomer', summary: 'Khách đang hoạt động', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{list}')])]
    public function index()
    {
        $list = Customer::query()
            ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 'n'))
            ->orderByDesc('customer_id')
            ->get()
            ->map(fn (Customer $c) => $this->toAdminArray($c))
            ->values()
            ->all();

        return LegacyJson::send(['list' => $list]);
    }

    #[OA\Get(path: '/home/GetListCustomer_Delete', summary: 'Khách vô hiệu / đã xóa', tags: [AppTags::ADMIN], responses: [new OA\Response(response: 200, description: '{list}')])]
    public function deleted()
    {
        $list = Customer::query()
            ->where(fn ($q) => $q->where('deleted', 'y')->orWhere('status', 'n'))
            ->orderByDesc('customer_id')
            ->get()
            ->map(fn (Customer $c) => $this->toAdminArray($c))
            ->values()
            ->all();

        return LegacyJson::send(['list' => $list]);
    }

    #[OA\Post(
        path: '/sysaccount/UpdateStatusCustomer',
        summary: 'Bật/tắt khách (toggle). y = hoạt động, n = vô hiệu.',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'customer_id', type: 'string'),
                new OA\Property(property: 'status', type: 'string', description: 'y|n'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{status:1}')]
    )]
    public function updateStatus(Request $request)
    {
        $customer = Customer::query()->where('customer_id', $request->input('customer_id'))->first();
        if (! $customer) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Không tìm thấy khách hàng']);
        }

        $disabled = $customer->deleted === 'y' || $customer->status === 'n';
        if ($disabled) {
            $customer->status = 'y';
            $customer->deleted = 'n';
        } else {
            $customer->status = 'n';
        }

        $customer->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    private function toAdminArray(Customer $customer): array
    {
        $devices = Device::query()
            ->where('customer_id', $customer->customer_id)
            ->where(fn ($q) => $q->whereNull('deleted')->orWhere('deleted', '!=', 'y'))
            ->get()
            ->map(fn (Device $d) => [
                'computer_id' => LegacyJson::str($d->computer_id),
                'computer_name' => LegacyJson::str($d->computer_name),
                'seri_computer' => LegacyJson::str($d->seri_computer),
                'ip_address' => LegacyJson::str($d->ip_address),
                'status' => LegacyJson::str($d->status),
                'customer_id' => LegacyJson::str($d->customer_id),
                'created_date' => LegacyJson::date($d->created_date),
            ])
            ->values()
            ->all();

        $row = $customer->toLegacyArray();
        $row['devices'] = $devices;
        $row['devices_new'] = [];

        return $row;
    }
}
