---
name: legacy-api
description: Write Laravel endpoints compatible with the old PHP Flutter contract (FormData, JSON string, typo keys). Use when adding /home, /sysaccount, /vietQR, or config6789.php routes.
---

# Legacy API

Helper bắt buộc:

```php
namespace App\Support;

final class LegacyJson
{
    public static function send(array $payload, int $http = 200)
    {
        return response(
            json_encode($payload, JSON_UNESCAPED_UNICODE),
            $http,
            ['Content-Type' => 'text/html; charset=utf-8']
        );
    }
}
```

- Routes: `routes/legacy.php`, middleware `api` (không CSRF), path đúng chữ hoa/thường app.
- POST: đọc FormData (`$request->input`), file qua `$request->file`.
- ID trên URL: string/int đều nhận.
- Serialize model: số → string trong JSON (`"12"`).
- Endpoint mẫu: xem `.agents/workflows.md` và Flutter `**/constants/*api.dart`.
