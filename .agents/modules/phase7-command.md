# Module: Lệnh điều khiển (legacy DB)

Phase: 7

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | POST `/home/CreateCommand` | FormData `sn`, `cmd_code`, `content` (app gửi `''`), `is_imme`, `second_wait` | `cmd_id` string | Có |
| Phone | Có | GET `/home/GetInfoCommand_ByID/{id}` | path cmd_id | `cmd_list` (`.first`; `DateTime.parse` `commit_time`/`return_time`; `int.parse` `second_wait` **string**; `is_imme == '1'`) | Có |
| Phone | Không | GetNewCommands / ReplyCommand | — | — | — |
| Phone | App-side | FCM tới `computer_token` **từ APK** sau CreateCommand | không qua Laravel | — | — |
| TV Flutter | Có | POST `/home/ReplyCommand/{cmd_id}` | FormData `return_value` | không parse | — |
| TV native | Có | GET `home/GetNewCommands_BySeriComputer/{serial}` mỗi **10s** khi `isFirebase=false` hoặc app không foreground | path `seri_computer` | `cmd_list` (Gson string fields) | JSON object |
| TV native | Có | POST `home/ReplyCommand/{cmd_id}` | `return_value` | — | — |
| Admin | Có | POST `/home/CreateCommand` | FormData giống Phone (`sn` = `serialComputer`) | `cmd_id` | Có |
| Admin | Không | GetInfo / GetNew / Reply | — | — | — |

Typo / exception: list `cmd_list`. `sn` = serial TV. `done`/`sync`/`is_imme` string `'0'`/`'1'`. Phone crash nếu `commit_time`/`return_time` rỗng hoặc `second_wait` không phải string. `return_value` rỗng/`null` = chưa reply (Phone GET 1s tới `second_wait`).

**Laravel không FCM / không RTDB.** User: phục hồi API cũ; TV tự GET. Phone/Admin vẫn có thể tự FCM từ APK — server không gửi.

`cmd_code` app: `RESTART_APP`, `WAKE_UP_APP`, `GET_TIMENOW`, `DELETE_DEVICE`, `DELETE_USER`, `VIDEO_STOP`, `VIDEO_PAUSE`, `VIDEO_RESTART`, `VIDEO_FROMUSB`, `VIDEO_FROMCAMP`, `OPEN_YOUTUBE`, `OPEN_NETFLIX`, `OPEN_SPOTIFY`, `OPEN_TS_Screen`, `OPEN_FORTUNE_WHEEL`, `OPEN_VIEON`, `OPEN_TIKTOK`, `OPEN_HOME`, `RESTART_DEVICE`, `UPDATE_VERSION`.

`return_value`: `OK`, `NOT_PLAY`, `CONTINUE_VIDEO`, `PAUSE_VIDEO`, `APP_NOT_SHOW`, `APP_LOCK`, `APP_RUNNING`, `NOT_PERMISSION`, hoặc `HH:mm:ss`.

## Cổng 2 — Database

Bảng: `tb_commands` (đã có Phase 0)  
Cột: `cmd_id`, `sn`, `cmd_code`, `content`, `is_imme`, `second_wait`, `commit_time`, `return_time`, `return_value`, `sync`, `done`  
Index: `sn`  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- Create: 1 insert; không FCM; không loop device.
- GetNew: 1 query pending `done='0'` và `sync` rỗng/`0`; claim `sync='1'` để TV không chạy lại lệnh mỗi 10s. Không rate-limit 30s (user: app GET 10s phải nhận được).
- GetInfo: 1 query theo PK.
- Reply: 1 update `return_value` + `done='1'`.
- JSON: mọi ID/số là string; `commit_time`/`return_time` luôn `Y-m-d H:i:s` (tạo lệnh: `return_time` = `commit_time` để Phone parse được).

## Cổng 4 — Dev

`CommandController`, `routes/legacy.php`, `DeviceCommand::toLegacyArray`, Swagger Phone/TV/Admin.

## Cổng 5 — Test

`tests/Feature/LegacyCommandTest.php` — 3 tests pass
