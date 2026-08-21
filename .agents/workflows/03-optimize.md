# Cổng 3 — Tối ưu (trước khi code)

Mục tiêu: thiết kế **không đốt hosting** khi nhiều TV/phone.

## Bắt buộc xem

- Số query / request: list + relation dùng `with()`, không N+1.
- Hạn mức gói: `App\Services\PacketQuota` — device `limit_qty`, media `limit_capacity`. Check **server**.
- Không poll lệnh 5s/10s. Heartbeat `UpdateAliveTimeDevice_ById` 60s được.
- Ghi/đọc file: stream, không load cả file RAM (Phase 5).
- List lớn: chỉ field app parse; ID string nhưng không dump blob.
- Endpoint dễ spam (GetNewCommands, GetInfoCommand): rate-limit khi tới Phase 7.
- Admin `.trim()` trên `detail` → luôn string `''`, không `null`.

## Ghi trên thẻ

Query plan ngắn, chỗ enforce quota, chỗ cache/rate-limit (nếu có).

## Xong khi

Có quyết định tối ưu bằng chữ, không “code rồi tính”.
