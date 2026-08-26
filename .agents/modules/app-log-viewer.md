# Module: AppLog + trang xem log

Phase: vận hành (mọi hosting)

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Không | — | — | — | — |
| TV | Không | — | — | — | — |
| Admin | Không | — | — | — | — |
| Dev/ops | Có | GET `/logs/app?key=` | query `key` = `LOG_VIEWER_KEY`; `file`, `q`, `level` | HTML | — |

Typo / exception: không phải API Flutter. Trang HTML. 403 nếu thiếu/sai key.

## Cổng 2 — Database

Không đổi DB: ☑  
File: `storage/logs/app/app-YYYY-MM-DD.log` (JSONL)

## Cổng 3 — Tối ưu

- Chỉ ghi `logs/app/` (không dump `laravel.log` lên web).
- Redact password/token. Bỏ `key=` trên URL trong log.
- Exception HTTP < 500 không ghi.
- Viewer đọc tối đa ~400KB cuối file. Không load cả disk.

**Mọi hosting:** disk/volume uploads phải lớn hơn tổng `limit_capacity` các gói đang active + reserve. `UPLOADS_VOLUME_CAP` nếu disk nhỏ hơn tổng gói. Quota gói không thay thế chỗ trống ổ cứng.

## Cổng 4 — Dev

`AppLog`, `LogViewerController`, view `logs/app`, route `/logs/app`, `bootstrap/app.php` report, MediaController dùng AppLog.

## Cổng 5 — Test

`tests/Feature/AppLogViewerTest.php` — pass
