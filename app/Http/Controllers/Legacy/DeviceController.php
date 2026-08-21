<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Device;
use App\Models\DeviceShare;
use App\OpenApi\AppTags;
use App\Services\PacketQuota;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DeviceController extends Controller
{
    #[OA\Post(path: '/home/CreateDevice', summary: 'TV pairing — seri_computer, quota limit_qty', tags: [AppTags::PROJECTOR, AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $serial = trim((string) $request->input('seri_computer'));
        $customer = Customer::query()->where('customer_id', $customerId)->first();

        if (! $customer || ! $customer->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách hàng']);
        }
        if ($serial === '') {
            return LegacyJson::send(['status' => -2, 'msg' => 'Thiếu seri_computer']);
        }

        $existing = Device::query()->where('seri_computer', $serial)->first();
        if ($existing && (string) $existing->customer_id !== (string) $customer->customer_id && $existing->deleted !== 'y') {
            return LegacyJson::send(['status' => -2, 'msg' => 'Thiết bị đã thuộc tài khoản khác']);
        }

        $isNew = ! $existing || $existing->deleted === 'y' || (string) $existing->customer_id !== (string) $customer->customer_id;
        if ($isNew && ! PacketQuota::canAddDevice($customer->customer_id)) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Đã hết hạn mức số TV của gói']);
        }

        $idDir = $request->input('id_dir');
        $idDir = ($idDir === '' || $idDir === null || $idDir === '0') ? null : $idDir;

        $payload = [
            'computer_name' => $request->input('computer_name'),
            'seri_computer' => $serial,
            'status' => $request->input('status', '1'),
            'provinces' => $request->input('provinces'),
            'district' => $request->input('district'),
            'wards' => $request->input('wards'),
            'center_id' => $request->input('center_id', '5'),
            'location' => $request->input('location'),
            'customer_id' => $customer->customer_id,
            'customer_name' => $customer->customer_name,
            'type' => $request->input('type', 'chủ sở hữu'),
            'id_dir' => $idDir,
            'time_end' => $request->input('time_end'),
            'deleted' => 'n',
            'created_by' => (string) $customer->customer_id,
            'actived_date' => now()->format('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $existing->fill($payload)->save();
        } else {
            Device::query()->create($payload);
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetDevices_ByCustomerId/{customerId}', summary: 'TV list device của khách', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byCustomer(string $customerId)
    {
        $list = Device::alive()
            ->with(['dir', 'customer'])
            ->where('customer_id', $customerId)
            ->orderBy('computer_id')
            ->get()
            ->map(fn (Device $d) => $d->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Get(path: '/home/GetDevice_ByComputerID/{computerId}', summary: 'Một thiết bị', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byComputerId(string $computerId)
    {
        $device = Device::alive()->with(['dir', 'customer'])->where('computer_id', $computerId)->first();
        $list = $device ? [$device->toLegacyArray()] : [];

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Get(path: '/home/GetDevice_ByIdDir/{idDir}', summary: 'Device trong dir (Phone + Admin)', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byDir(string $idDir)
    {
        $list = Device::alive()
            ->with(['dir', 'customer'])
            ->where('id_dir', $idDir)
            ->orderBy('computer_id')
            ->get()
            ->map(fn (Device $d) => $d->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Get(path: '/home/GetDevicesNotBelongAnyDir_ByCustomerId/{customerId}', summary: 'Device chưa gán dir', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function notInDir(string $customerId)
    {
        $list = Device::alive()
            ->with(['dir', 'customer'])
            ->where('customer_id', $customerId)
            ->where(function ($q): void {
                $q->whereNull('id_dir')->orWhere('id_dir', 0);
            })
            ->orderBy('computer_id')
            ->get()
            ->map(fn (Device $d) => $d->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Post(path: '/home/UpDateDevice_ById/{computerId}', summary: 'Cập nhật device / gán dir', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function update(Request $request, string $computerId)
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thiết bị']);
        }

        $idDir = $request->input('id_dir', $device->id_dir);
        $idDir = ($idDir === '' || $idDir === '0') ? null : $idDir;

        $device->fill([
            'computer_name' => $request->input('computer_name', $device->computer_name),
            'seri_computer' => $request->input('seri_computer', $device->seri_computer),
            'status' => $request->input('status', $device->status),
            'provinces' => $request->input('provinces', $device->provinces),
            'district' => $request->input('district', $device->district),
            'wards' => $request->input('wards', $device->wards),
            'center_id' => $request->input('center_id', $device->center_id),
            'location' => $request->input('location', $device->location),
            'type' => $request->input('type', $device->type),
            'id_dir' => $idDir,
            'time_end' => $request->input('time_end', $device->time_end),
        ])->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/DeleteDevice_ById/{computerId}', summary: 'Xóa mềm device', tags: [AppTags::CUSTOMER, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function destroy(string $computerId)
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thiết bị']);
        }

        $device->deleted = 'y';
        $device->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetListDeviceOfCamp_ByCampId/{campaignId}', summary: 'Device của camp — key Dir_list', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function ofCampaign(string $campaignId)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if (! $camp) {
            return LegacyJson::send(['Dir_list' => []]);
        }

        $query = Device::alive()->with(['dir', 'customer']);
        if ($camp->computer_id) {
            $query->where('computer_id', $camp->computer_id);
        } elseif ($camp->id_dir) {
            $query->where('id_dir', $camp->id_dir);
        } else {
            return LegacyJson::send(['Dir_list' => []]);
        }

        $list = $query->get()->map(fn (Device $d) => $d->toLegacyArray())->values()->all();

        return LegacyJson::send(['Dir_list' => $list]);
    }

    #[OA\Post(path: '/home/InsertDeviceShare', summary: 'Chia sẻ device', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function share(Request $request)
    {
        $computerId = $request->input('computer_id');
        $from = $request->input('customer_idfrom');
        $to = $request->input('customer_idto');
        $device = Device::alive()->where('computer_id', $computerId)->first();
        $target = Customer::query()->where('customer_id', $to)->first();

        if (! $device) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thiết bị']);
        }
        if (! $target || ! $target->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách nhận']);
        }

        $exists = DeviceShare::query()
            ->where('computer_id', $computerId)
            ->where('customer_idto', $to)
            ->exists();
        if ($exists) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Đã chia sẻ thiết bị này']);
        }

        DeviceShare::query()->create([
            'computer_id' => $computerId,
            'id_dir' => $device->id_dir,
            'customer_idfrom' => $from,
            'customer_idto' => $to,
            'checkOwner' => (string) $request->input('checkOwner', '0') === '1' ? '1' : '0',
            'created_date' => now(),
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetSharedCustomerList_ByComputeID/{computerId}', summary: 'Typo ComputeID — khách được share device', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedCustomers(string $computerId)
    {
        $tos = DeviceShare::query()->where('computer_id', $computerId)->pluck('customer_idto');
        $list = Customer::query()
            ->whereIn('customer_id', $tos)
            ->get()
            ->map(fn (Customer $c) => $c->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['userList' => $list]);
    }

    #[OA\Get(path: '/home/GetDeviceCustomer_SharedById/{customerId}', summary: 'Device được share tới khách', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedToCustomer(string $customerId)
    {
        $shares = DeviceShare::query()->where('customer_idto', $customerId)->get()->keyBy('computer_id');
        $list = Device::alive()
            ->with(['dir', 'customer'])
            ->whereIn('computer_id', $shares->keys())
            ->get()
            ->map(function (Device $d) use ($shares) {
                $share = $shares->get($d->computer_id);

                return $d->toLegacyArray([
                    'is_owner' => LegacyJson::str($share?->checkOwner === '1' ? '1' : '0'),
                ]);
            })
            ->values()
            ->all();

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Get(path: '/home/GetSharedDevices_ByCustomerId/{customerId}', summary: 'Device khách đã chia sẻ đi', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedFromCustomer(string $customerId)
    {
        $shares = DeviceShare::query()->where('customer_idfrom', $customerId)->get();
        $devices = Device::alive()
            ->whereIn('computer_id', $shares->pluck('computer_id'))
            ->get()
            ->keyBy('computer_id');
        $tos = Customer::query()
            ->whereIn('customer_id', $shares->pluck('customer_idto'))
            ->get()
            ->keyBy('customer_id');

        $list = [];
        foreach ($shares as $share) {
            $device = $devices->get($share->computer_id);
            $to = $tos->get($share->customer_idto);
            $list[] = [
                'computer_id' => LegacyJson::str($share->computer_id),
                'computer_name' => LegacyJson::str($device?->computer_name),
                'seri_computer' => LegacyJson::str($device?->seri_computer),
                'customer_id' => LegacyJson::str($share->customer_idto),
                'id_dir' => LegacyJson::str($share->id_dir ?: $device?->id_dir),
                'type' => LegacyJson::str($device?->type),
                'customer_name' => LegacyJson::str($to?->customer_name),
                'computer_token' => LegacyJson::str($device?->computer_token),
                'phone_number' => LegacyJson::str($to?->phone_number),
                'email' => LegacyJson::str($to?->email),
                'lasted_alive_time' => LegacyJson::date($device?->lasted_alive_time),
                'rom_memory_total' => LegacyJson::str($device?->rom_memory_total),
                'rom_memory_used' => LegacyJson::str($device?->rom_memory_used),
                'is_owner' => $share->checkOwner === '1',
            ];
        }

        return LegacyJson::send(['Device_list' => $list]);
    }

    #[OA\Get(path: '/home/DeleteDevice_shared/{computerId}/{customerId}', summary: 'Hủy share device', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function deleteShare(string $computerId, string $customerId)
    {
        DeviceShare::query()
            ->where('computer_id', $computerId)
            ->where('customer_idto', $customerId)
            ->delete();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/UpdateRomMemory/{computerId}', summary: 'TV heartbeat ROM', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function updateRom(Request $request, string $computerId)
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thiết bị']);
        }

        $device->rom_memory_total = $request->input('rom_memory_total');
        $device->rom_memory_used = $request->input('rom_memory_used');
        $device->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/UpdateComputerToken_ById/{computerId}/{token}', summary: 'TV FCM token', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function updateToken(string $computerId, string $token)
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if ($device) {
            $device->computer_token = $token;
            $device->save();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/UpdateAliveTimeDevice_ById/{computerId}', summary: 'Heartbeat 60s', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function updateAlive(string $computerId)
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if ($device) {
            $device->lasted_alive_time = now();
            $device->save();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }
}
