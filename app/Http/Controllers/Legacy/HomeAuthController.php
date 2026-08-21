<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Device;
use App\OpenApi\AppTags;
use App\Services\OtpService;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HomeAuthController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    #[OA\Post(
        path: '/home/register',
        summary: 'Customer register',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['email', 'password'], properties: [
                new OA\Property(property: 'customer_name', type: 'string'),
                new OA\Property(property: 'phone_number', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'fcm_token', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'JSON {status:"1", msg, id}')]
    )]
    public function register(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $password = (string) $request->input('password');

        if ($email === '' || $password === '') {
            return LegacyJson::send(['status' => '0', 'msg' => 'Email và mật khẩu bắt buộc', 'id' => null]);
        }

        if (Customer::query()->where('email', $email)->exists()) {
            return LegacyJson::send(['status' => '0', 'msg' => 'Email đã tồn tại', 'id' => null]);
        }

        $customer = Customer::query()->create([
            'customer_name' => trim((string) $request->input('customer_name')),
            'phone_number' => trim((string) $request->input('phone_number')),
            'email' => $email,
            'password' => $password,
            'fcm_token' => $request->input('fcm_token'),
            'login_with' => 'email',
            'status' => 'y',
            'deleted' => 'n',
        ]);

        return LegacyJson::send([
            'status' => '1',
            'msg' => 'Đăng ký thành công',
            'id' => LegacyJson::str($customer->customer_id),
        ]);
    }

    #[OA\Post(
        path: '/home/login',
        summary: 'Customer login (plaintext). Field email = email hoặc SĐT. Empty password = Google/Apple. Phone + TV.',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['email'], properties: [
                new OA\Property(property: 'email', type: 'string', description: 'Email hoặc số điện thoại'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'fcm_token', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'JSON {status:1, msg, info:[User]}')]
    )]
    public function login(Request $request)
    {
        $login = trim((string) $request->input('email'));
        $password = (string) $request->input('password', '');

        $customer = Customer::query()
            ->where(function ($q) use ($login): void {
                $q->where('email', $login)->orWhere('phone_number', $login);
            })
            ->first();

        if (! $customer || ! $customer->isActive() || ! $customer->passwordMatches($password)) {
            return LegacyJson::send([
                'status' => 0,
                'msg' => 'Tài khoản hoặc mật khẩu không chính xác.',
                'info' => [],
            ]);
        }

        if ($token = $request->input('fcm_token')) {
            $customer->fcm_token = $token;
            $customer->save();
        }

        if ($password === '' && $customer->login_with === 'email') {
            $customer->login_with = 'google';
            $customer->save();
        }

        return LegacyJson::send([
            'status' => 1,
            'msg' => 'OK',
            'info' => [$customer->toLegacyArray($password)],
        ]);
    }

    #[OA\Post(
        path: '/home/logout1',
        summary: 'Clear FCM token',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'fcm_token', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'JSON {status:1, msg, info:[]}')]
    )]
    public function logout(Request $request)
    {
        $token = (string) $request->input('fcm_token');

        if ($token !== '') {
            Customer::query()->where('fcm_token', $token)->update(['fcm_token' => null]);
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK', 'info' => []]);
    }

    #[OA\Post(
        path: '/home/SendCode',
        summary: 'Send OTP to customer email',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['email'], properties: [
                new OA\Property(property: 'email', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status != -1 OK; status -1 + msg if email missing')]
    )]
    public function sendCode(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $exists = Customer::query()->where('email', $email)->exists();

        if (! $exists) {
            return LegacyJson::send(['status' => -1, 'msg' => 'Email không tồn tại']);
        }

        $this->otp->send($email, 'reset_customer');

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/home/resetpass',
        summary: 'Reset customer password with OTP',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['email', 'code_authen', 'password'], properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'code_authen', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1 or msg')]
    )]
    public function resetPassword(Request $request)
    {
        $email = trim((string) $request->input('email'));
        $code = (string) $request->input('code_authen');
        $password = (string) $request->input('password');

        if (! $this->otp->consume($email, 'reset_customer', $code)) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Mã xác thực không đúng hoặc đã hết hạn']);
        }

        $customer = Customer::query()->where('email', $email)->first();
        $customer->password = $password;
        $customer->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/home/changepass',
        summary: 'Change customer password',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password_old', type: 'string'),
                new OA\Property(property: 'password_new', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1 or -2 + msg')]
    )]
    public function changePassword(Request $request)
    {
        $customer = Customer::query()->where('email', trim((string) $request->input('email')))->first();
        $old = (string) $request->input('password_old');

        if (! $customer || ! $customer->passwordMatches($old) || $old === '') {
            return LegacyJson::send(['status' => -2, 'msg' => 'Mật khẩu cũ không đúng']);
        }

        $customer->password = (string) $request->input('password_new');
        $customer->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/home/DeleteUser1',
        summary: 'Soft-delete customer by email',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'email', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function deleteUser(Request $request)
    {
        $customer = Customer::query()->where('email', trim((string) $request->input('email')))->first();

        if (! $customer) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Không tìm thấy tài khoản']);
        }

        $customer->deleted = 'y';
        $customer->status = 'n';
        $customer->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(
        path: '/home/GetInfoCustomer_ById/{id}',
        summary: 'Customer by id — phone + TV',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'JSON {status, msg, userList}')]
    )]
    public function getById(string $id)
    {
        $customer = Customer::query()->where('customer_id', $id)->first();

        return LegacyJson::send([
            'status' => 1,
            'msg' => 'OK',
            'userList' => $customer ? [$customer->toLegacyArray()] : [],
        ]);
    }

    #[OA\Get(
        path: '/home/GetInfoCustomer_ByEmail/{email}',
        summary: 'Customer by email — phone + TV Google login',
        tags: [AppTags::CUSTOMER, AppTags::PROJECTOR],
        parameters: [new OA\Parameter(name: 'email', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'JSON {status, msg, userList}')]
    )]
    public function getByEmail(string $email)
    {
        $customer = Customer::query()->where('email', $email)->first();

        return LegacyJson::send([
            'status' => 1,
            'msg' => 'OK',
            'userList' => $customer ? [$customer->toLegacyArray()] : [],
        ]);
    }

    #[OA\Post(
        path: '/home/UpdateInfoCustomer_ById/{id}',
        summary: 'Update customer profile',
        tags: [AppTags::CUSTOMER],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'customer_name', type: 'string'),
                new OA\Property(property: 'date_of_birth', type: 'string'),
                new OA\Property(property: 'address', type: 'string'),
                new OA\Property(property: 'phone_number', type: 'string'),
                new OA\Property(property: 'sex', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function updateById(Request $request, string $id)
    {
        $customer = Customer::query()->where('customer_id', $id)->first();

        if (! $customer) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Không tìm thấy tài khoản']);
        }

        $customer->fill([
            'email' => $request->input('email', $customer->email),
            'customer_name' => $request->input('customer_name', $customer->customer_name),
            'date_of_birth' => $request->input('date_of_birth', $customer->date_of_birth),
            'address' => $request->input('address', $customer->address),
            'phone_number' => $request->input('phone_number', $customer->phone_number),
            'sex' => $request->input('sex', $customer->sex),
        ]);
        $customer->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(
        path: '/home/GetListCustomer_Bysericomputer/{serial}',
        summary: 'TV: serial đã thuộc customer nào (chặn login nếu máy người khác)',
        tags: [AppTags::PROJECTOR],
        parameters: [new OA\Parameter(name: 'serial', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'JSON {list:[{customer_id,...}]}')]
    )]
    public function getBySerial(string $serial)
    {
        $customerIds = Device::query()
            ->where('seri_computer', $serial)
            ->where('deleted', '!=', 'y')
            ->pluck('customer_id');

        $list = Customer::query()
            ->whereIn('customer_id', $customerIds)
            ->get()
            ->map(fn (Customer $c) => $c->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['status' => 1, 'list' => $list]);
    }
}
