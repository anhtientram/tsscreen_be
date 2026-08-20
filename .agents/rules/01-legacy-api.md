# Legacy API contract

```php
// GOOD
return response(json_encode($payload), 200, ['Content-Type' => 'text/html; charset=utf-8']);

// BAD — app crash
return response()->json($payload);
```

List keys không thống nhất — copy đúng app:

`info`, `userList`, `accountList`, `list`, `Packet_list`, `Device_list`, `Dir_list`, `camp_list`, `Camp_list`, `file_list`, `cmd_list`, `orderList`, `transaction_list`, `Profile_list`, `Nofity_list`, `camp_list_time`

Password:

- Customer login/register: plaintext. Google/Apple: password rỗng nếu email đã có.
- Admin (`/sysaccount/*`) + TV check admin: MD5 hex.
- DB: bcrypt chuỗi nhận được. JSON vẫn có key `password` (phone lưu token) — **không** trả hash.

Admin `user_type`: `"1"` Super, `"2"` Admin, `"3"` Member.
Customer status API: `"y"` bật, `"n"` tắt.
Order `pay`: `"0"` mới, `"1"` đã active.
