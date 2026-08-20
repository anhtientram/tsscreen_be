<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\OpenApi\AppTags;
use App\Services\OtpService;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class SysAccountController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    #[OA\Post(
        path: '/sysaccount/login',
        summary: 'Admin login (password MD5). TV cũng gọi khi đăng nhập tài khoản admin trên box.',
        tags: [AppTags::ADMIN, AppTags::PROJECTOR],
        requestBody: new OA\RequestBody(required: true, content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(required: ['username', 'password'], properties: [
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'password', type: 'string', description: 'MD5 hex'),
                new OA\Property(property: 'fcm_token', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'JSON {status:1, accountList}')]
    )]
    public function login(Request $request)
    {
        $username = trim((string) $request->input('username'));
        $password = (string) $request->input('password');

        $account = Account::query()
            ->where('username', $username)
            ->where('deleted', '!=', 'y')
            ->first();

        if (! $account || ! $account->passwordMatches($password)) {
            return LegacyJson::send(['status' => 0, 'accountList' => []]);
        }

        if ($token = $request->input('fcm_token')) {
            $account->fcm_token = $token;
            $account->save();
        }

        return LegacyJson::send([
            'status' => 1,
            'accountList' => [$account->toLegacyArray()],
        ]);
    }

    #[OA\Post(
        path: '/sysaccount/changepass',
        summary: 'Admin change password (MD5 fields)',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'password_old', type: 'string'),
                new OA\Property(property: 'password_new', type: 'string'),
                new OA\Property(property: 'password_ag', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function changePassword(Request $request)
    {
        $account = Account::query()
            ->where('username', trim((string) $request->input('username')))
            ->first();

        $old = (string) $request->input('password_old');
        $new = (string) $request->input('password_new');
        $again = (string) $request->input('password_ag');

        if (! $account || ! $account->passwordMatches($old)) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Mật khẩu cũ không đúng']);
        }

        if ($again !== '' && $new !== $again) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Mật khẩu nhắc lại không khớp']);
        }

        $account->password = $new;
        $account->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/sysaccount/createaccount',
        summary: 'Create admin account (password MD5)',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'phone_number', type: 'string'),
                new OA\Property(property: 'user_type', type: 'string', description: '1 Super, 2 Admin, 3 Member'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function createAccount(Request $request)
    {
        $username = trim((string) $request->input('username'));

        if ($username === '' || Account::query()->where('username', $username)->exists()) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Username không hợp lệ hoặc đã tồn tại']);
        }

        Account::query()->create([
            'username' => $username,
            'password' => (string) $request->input('password'),
            'email' => trim((string) $request->input('email')),
            'phone_number' => trim((string) $request->input('phone_number')),
            'user_type' => (string) $request->input('user_type', '3'),
            'deleted' => 'n',
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/sysaccount/SendCode',
        summary: 'Send OTP to admin email',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'email', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function sendCode(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if (! Account::query()->where('email', $email)->exists()) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Email không tồn tại']);
        }

        $this->otp->send($email, 'reset_admin');

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(
        path: '/sysaccount/resetpass',
        summary: 'Admin reset password (password MD5)',
        tags: [AppTags::ADMIN],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'code_authen', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: 'status 1')]
    )]
    public function resetPassword(Request $request)
    {
        $email = trim((string) $request->input('email'));

        if (! $this->otp->consume($email, 'reset_admin', (string) $request->input('code_authen'))) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Mã xác thực không đúng']);
        }

        $account = Account::query()->where('email', $email)->first();
        $account->password = (string) $request->input('password');
        $account->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(
        path: '/sysaccount/GetListAccount',
        summary: 'Danh sách admin. Phone lấy account_id để InsertNotify_Account.',
        tags: [AppTags::ADMIN, AppTags::CUSTOMER],
        responses: [new OA\Response(response: 200, description: 'JSON {accountList}')]
    )]
    public function listAccounts()
    {
        $list = Account::query()
            ->where('deleted', '!=', 'y')
            ->get()
            ->map(fn (Account $a) => $a->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['accountList' => $list]);
    }
}
