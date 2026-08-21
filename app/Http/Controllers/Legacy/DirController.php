<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DirectoryFolder;
use App\Models\DirShare;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class DirController extends Controller
{
    #[OA\Post(path: '/home/CreateDir', summary: 'Phone tạo nhóm thiết bị; msg = id_dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'status 1 msg id_dir')])]
    public function create(Request $request)
    {
        $customerId = $request->input('customer_id');
        $customer = Customer::query()->where('customer_id', $customerId)->first();
        if (! $customer || ! $customer->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách hàng']);
        }

        $dir = DirectoryFolder::query()->create([
            'name_dir' => $request->input('name_dir'),
            'customer_id' => $customer->customer_id,
            'type_dir' => $request->input('type_dir'),
            'deleted' => 'n',
            'created_by' => (string) $customer->customer_id,
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($dir->id_dir)]);
    }

    #[OA\Get(path: '/home/GetDirCustomer_ById/{customerId}', summary: 'Dir của khách (Phone + TV + Admin)', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR, AppTags::ADMIN], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byCustomer(string $customerId)
    {
        $list = DirectoryFolder::alive()
            ->where('customer_id', $customerId)
            ->orderBy('id_dir')
            ->get()
            ->map(fn (DirectoryFolder $d) => $d->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['Dir_list' => $list]);
    }

    #[OA\Get(path: '/home/GetDir_ById/{idDir}', summary: 'Một dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byId(string $idDir)
    {
        $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
        $list = $dir ? [$dir->toLegacyArray()] : [];

        return LegacyJson::send(['Dir_list' => $list]);
    }

    #[OA\Get(path: '/home/GetDirCustomer_SharedById/{customerId}', summary: 'Dir được chia sẻ tới khách', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedToCustomer(string $customerId)
    {
        $shareIds = DirShare::query()
            ->where('customer_idto', $customerId)
            ->pluck('id_dir', 'id_dir')
            ->all();

        $shares = DirShare::query()
            ->where('customer_idto', $customerId)
            ->get()
            ->keyBy('id_dir');

        $list = DirectoryFolder::alive()
            ->whereIn('id_dir', array_keys($shareIds))
            ->with('customer')
            ->get()
            ->map(function (DirectoryFolder $d) use ($shares) {
                $share = $shares->get($d->id_dir);

                return $d->toLegacyArray([
                    'is_owner' => LegacyJson::str($share?->checkOwner === '1' ? '1' : '0'),
                ]);
            })
            ->values()
            ->all();

        return LegacyJson::send(['Dir_list' => $list]);
    }

    #[OA\Get(path: '/home/GetShareDir_ByCustomerId/{customerId}', summary: 'Dir khách đã chia sẻ đi', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedFromCustomer(string $customerId)
    {
        $shares = DirShare::query()
            ->where('customer_idfrom', $customerId)
            ->get();

        $dirs = DirectoryFolder::alive()
            ->whereIn('id_dir', $shares->pluck('id_dir'))
            ->get()
            ->keyBy('id_dir');

        $tos = Customer::query()
            ->whereIn('customer_id', $shares->pluck('customer_idto'))
            ->get()
            ->keyBy('customer_id');

        $list = [];
        foreach ($shares as $share) {
            $dir = $dirs->get($share->id_dir);
            $to = $tos->get($share->customer_idto);
            $list[] = [
                'id_dir' => LegacyJson::str($share->id_dir),
                'name_dir' => LegacyJson::str($dir?->name_dir),
                'customer_id' => LegacyJson::str($share->customer_idfrom),
                'customer_idto' => LegacyJson::str($share->customer_idto),
                'type_dir' => LegacyJson::str($dir?->type_dir),
                'customer_name' => LegacyJson::str($to?->customer_name),
                'phone_number' => LegacyJson::str($to?->phone_number),
                'email' => LegacyJson::str($to?->email),
            ];
        }

        return LegacyJson::send(['Dir_list' => $list]);
    }

    #[OA\Post(path: '/home/UpDateDir_ById/{idDir}', summary: 'Sửa tên/loại dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function update(Request $request, string $idDir)
    {
        $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
        if (! $dir) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thư mục']);
        }

        $dir->fill([
            'name_dir' => $request->input('name_dir', $dir->name_dir),
            'type_dir' => $request->input('type_dir', $dir->type_dir),
            'last_MDF_by' => LegacyJson::str($request->input('customer_id')),
        ])->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/DeleteDir_ById/{idDir}', summary: 'Xóa mềm dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function destroy(string $idDir)
    {
        $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
        if (! $dir) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thư mục']);
        }

        $dir->deleted = 'y';
        $dir->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/InsertDirShare', summary: 'Chia sẻ dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function share(Request $request)
    {
        $idDir = $request->input('id_dir');
        $from = $request->input('customer_idfrom');
        $to = $request->input('customer_idto');
        $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
        $target = Customer::query()->where('customer_id', $to)->first();

        if (! $dir) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thư mục']);
        }
        if (! $target || ! $target->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách nhận']);
        }
        if ((string) $from === (string) $to) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không thể chia sẻ cho chính mình']);
        }

        $exists = DirShare::query()
            ->where('id_dir', $idDir)
            ->where('customer_idto', $to)
            ->exists();
        if ($exists) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Đã chia sẻ thư mục này']);
        }

        DirShare::query()->create([
            'id_dir' => $idDir,
            'customer_idfrom' => $from,
            'customer_idto' => $to,
            'checkOwner' => (string) $request->input('checkOwner', '0') === '1' ? '1' : '0',
            'created_date' => now(),
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetSharedCustomerList_ByDirID/{idDir}', summary: 'Danh sách khách được share dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedCustomers(string $idDir)
    {
        $tos = DirShare::query()->where('id_dir', $idDir)->pluck('customer_idto');
        $list = Customer::query()
            ->whereIn('customer_id', $tos)
            ->get()
            ->map(fn (Customer $c) => $c->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['status' => 1, 'userList' => $list]);
    }

    #[OA\Get(path: '/home/DeleteDir_shared/{idDir}/{customerId}', summary: 'Hủy chia sẻ dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function deleteShare(string $idDir, string $customerId)
    {
        DirShare::query()
            ->where('id_dir', $idDir)
            ->where('customer_idto', $customerId)
            ->delete();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/UpDateOnOffDeviceDir_ById/{idDir}', summary: 'Giờ bật/tắt theo dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function updateOnOff(Request $request, string $idDir)
    {
        $dir = DirectoryFolder::alive()->where('id_dir', $idDir)->first();
        if (! $dir) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy thư mục']);
        }

        $dir->turnon_time = $request->input('turnon_time');
        $dir->turnoff_time = $request->input('turnoff_time');
        $dir->last_MDF_by = LegacyJson::str($request->input('customer_id'));
        $dir->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }
}
