# Module: Notify in-app

Phase: 6

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | GET `/home/GetNofity_ByIdCustomer/{customerId}` | path customer_id | `Nofity_list` | Có |
| Phone | Có | GET `/home/GetNofityNew_ByIdCustomer/{customerId}` | path | `{ count }` số | Có |
| Phone | Có | GET `/home/GetNofity_ById/{id}` | path id_notify | `Nofity_list` (lấy `.first`) | Có |
| Phone | Có | GET `/home/UpdateNotify/{id}` | path | không parse | Có (bỏ qua) |
| Phone | Có | POST `/home/InsertNotify` | FormData `customer_id`, `title`, `descript`, `detail`, `picture` | không parse | — |
| Phone | Có | POST `/home/InsertNotify_Account` | FormData `account_id`, `title`, `descript`, `detail`, `picture` | không parse | — |
| TV | Có | POST `/home/InsertNotify` | FormData giống Phone (`customer_id` thật) | không parse | — |
| TV | Không | list/count/update | — | — | — |
| Admin | Có | GET `/home/GetNofity_ByIdAccount/{accountId}` | path account_id | `Nofity_list` | Có |
| Admin | Có | GET `/home/GetNofityNew_ByIdAccount/{accountId}` | path | `{ count }` | Có |
| Admin | Có | GET `/home/GetNofity_ById/{id}` | giống Phone | `Nofity_list` | Có |
| Admin | Có | GET `/home/UpdateNotify/{id}` | path | — | — |
| Admin | Có | POST `/home/InsertNotify` | FormData `customer_id` = **order.customerId** (property Dart tên `accountId`), `title`, `descript`, `detail`, `picture` | — | — |

Typo / exception: `Nofity` (không phải Notify); list `Nofity_list`; field `descript`. `seen` so sánh `'0'` chưa đọc. Admin `InsertNotify`: Dart `accountId` = `order.customerId` → FormData `customer_id` (notify **customer**). Inbox admin: Phone `InsertNotify_Account`. `count` JSON number. `id_notify` string.

**Không FCM:** user yêu cầu chỉ in-app DB. Firebase/local push note lại Phase 7; không gửi FCM khi InsertNotify.

## Cổng 2 — Database

Bảng: `tb_notifications` (`customer_id`), `tb_account_notifications` (`account_id`)  
Cột: `id_notify`, `title`, `descript`, `detail`, `picture`, `seen` default `'0'`, `created_date`  
Index: đã có `customer_id` / `account_id`  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- List: 1 query `orderByDesc id_notify`, không N+1.
- Count mới: `where seen = '0'` + index sẵn, không load list.
- Update: 1–2 query theo id (customer rồi account); không poll.
- Insert: không FCM, không loop N admin trên server (Phone tự loop `InsertNotify_Account`).
- String rỗng cho `title`/`descript`/`detail`/`picture` (admin/phone không `.trim()` bắt buộc nhưng tránh null).
- `id_notify` JSON string (Admin `as String?`).

## Cổng 4 — Dev

`NotifyController`, `routes/legacy.php`, models `toLegacyArray`, Swagger AppTags Phone/TV/Admin.

## Cổng 5 — Test

`tests/Feature/LegacyNotifyTest.php` — 4 tests pass
