# Cổng 1 — Phân tích 3 app

Mục tiêu: contract **copy từ Flutter**, không bịa field.

## Nguồn

| App | Path / request / model |
|-----|------------------------|
| Phone | `RemoteProjector2024/lib/constants/app_api.dart`, `lib/requests/`, `lib/models/` |
| TV | `remote_projector_tv/lib/constants/api.dart`, `lib/request/`, `lib/models/` |
| Admin | `RemoteProjectorAdmin/lib/constants/app_api.dart`, `lib/request/`, `lib/model/` |

## Việc làm

1. Grep path (ví dụ `CreateDevice`) trên **cả 3** repo.
2. Với mỗi app có gọi: HTTP method, URL (giữ nguyên hoa thường), body FormData hay query/path.
3. Response: `jsonDecode(response.data)` hay `response.data['key']` (hai case VietQR + transactions → `forceJson`).
4. `fromJson`: key bắt buộc, `.trim()` (không được null), list key (`Packet_list` ≠ `orderList`).
5. Typo giữ nguyên: `Nofity`, `vaild_date`, `seri_computer`, `url_youtobe`, `descript`, `Genaral`.
6. Ghi [templates/module-card.md](../templates/module-card.md).

## Xong khi

Thẻ module có đủ: apps, path, request keys, response shape, exceptions. Chưa đoán “giống PHP cũ”.
