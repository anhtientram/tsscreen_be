# Legacy API contract

```php
return LegacyJson::send($payload);
// App: text/html JSON string. Swagger Try it out: application/json pretty.
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

Swagger tag theo 3 app: `Customer (Phone)`, `Projector (TV)`, `Admin` (`App\OpenApi\AppTags`). Endpoint dùng chung gắn đủ tag.
