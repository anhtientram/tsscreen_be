<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Models\Order;
use App\OpenApi\AppTags;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class VietQrController extends Controller
{
    #[OA\Post(
        path: '/vietQR/getQRCode_ByPaidId',
        summary: 'Phone đọc response.data[qrLink] không jsonDecode — luôn application/json. Chưa có webhook; admin kích hoạt tay.',
        tags: [AppTags::CUSTOMER],
        requestBody: new OA\RequestBody(content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(properties: [
                new OA\Property(property: 'paid_id', type: 'string'),
            ])
        )),
        responses: [new OA\Response(response: 200, description: '{qrLink}')]
    )]
    public function getQrCode(Request $request)
    {
        $order = Order::query()->where('paid_id', $request->input('paid_id'))->first();
        if (! $order) {
            return LegacyJson::send(['qrLink' => ''], forceJson: true);
        }

        $cfg = AppConfig::map();
        $bin = $cfg['VIETQR_BANK_BIN'] ?? '';
        $account = $cfg['VIETQR_ACCOUNT'] ?? '';
        $name = rawurlencode((string) ($cfg['VIETQR_ACCOUNT_NAME'] ?? 'TS Screen'));
        $info = rawurlencode((string) ($order->reg_number ?: 'DH'.$order->paid_id));
        $amount = (int) preg_replace('/\D+/', '', (string) $order->price);

        if ($bin !== '' && $account !== '') {
            $qrLink = sprintf(
                'https://img.vietqr.io/image/%s-%s-compact2.png?amount=%d&addInfo=%s&accountName=%s',
                rawurlencode($bin),
                rawurlencode($account),
                $amount,
                $info,
                $name
            );
        } else {
            $qrLink = url('/vietQR/page/'.$order->paid_id);
        }

        return LegacyJson::send(['qrLink' => $qrLink], forceJson: true);
    }

    public function page(string $paidId)
    {
        $order = Order::query()->where('paid_id', $paidId)->first();
        if (! $order) {
            return response('Không tìm thấy đơn hàng', 404);
        }

        $amount = e(LegacyJson::money($order->price));
        $reg = e($order->reg_number ?: 'DH'.$order->paid_id);
        $name = e($order->name_packet);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Thanh toán {$reg}</title>
<style>
body{font-family:sans-serif;padding:24px;color:#222}
.box{max-width:420px;margin:auto;border:1px solid #ddd;border-radius:12px;padding:20px}
h1{font-size:18px}
.muted{color:#666;font-size:14px}
</style></head><body>
<div class="box">
<h1>Chuyển khoản gói {$name}</h1>
<p>Mã đơn: <b>{$reg}</b></p>
<p>Số tiền: <b>{$amount}</b></p>
<p class="muted">Chưa cấu hình VIETQR_BANK_BIN / VIETQR_ACCOUNT trên server. Admin sẽ kích hoạt đơn sau khi nhận tiền.</p>
</div></body></html>
HTML;

        return response($html, 200, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}
