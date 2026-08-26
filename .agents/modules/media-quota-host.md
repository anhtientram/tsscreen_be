# Module: Media quota GB + chống đầy hosting

Phase: 5 (tối ưu sau Phase 7)

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | POST uploadfile_customer / _large / getsizeofdir_customer / deletefile_customer | name_dir token; fileupload; large: filename chunk_index total_chunks customer_id | status 1 hoặc true; `msg` khi fail; totalsize string; path_file | Có |
| Phone | Có | So sánh totalsize + file vs `limit_capacity` (bytes string, `int.tryParse`) | client-side; **server vẫn phải chặn** | toast `msg` | Có |
| TV | HEAD/GET `/uploads/{token}/{file}` | Range seek video | Content-Type image/ hoặc video/ | — |
| Admin | Có | CreatePacket/UpdatePacket `limit_capacity` | FormData string | Packet_list | Có |

Typo / exception: `limit_capacity` JSON **string bytes** (Phone `formatBytes`). Admin nhập `1`…`1024` = GB; số lớn hơn = bytes. status fail: 0 hoặc -2 + `msg`. Không đổi path.

## Cổng 2 — Database

Bảng: `tb_packets.limit_capacity`, `tb_orders.limit_capacity`, `tb_resources.file_size`, `tb_upload_chunks`  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- Gói: 1GB packet = 1 GiB upload (`limit_capacity` bytes). `used + incoming - file cùng tên <= limit`.
- Chunk: ước lượng `total_chunks * chunk_size` **trước** khi ghi `.part` — không để video lớn đổ đầy volume rồi mới fail lúc ghép.
- Hosting: **mọi nhà cung cấp** — check free space volume uploads + reserve 64MB. `UPLOADS_VOLUME_CAP` khi ổ nhỏ hơn tổng gói. Xem `.agents/rules/05-hosting.md`.
- Trần volume tùy chọn `UPLOADS_VOLUME_CAP` (tổng mọi user) khi volume nhỏ hơn tổng gói.
- Ghép chunk: copy xong xóa từng `.part` (không giữ 2 bản đủ file).
- Tối đa 2 upload/user **và** 2 upload toàn instance (4 PHP thread).
- Prune `.part*` > 24h opportunistic mỗi giờ khi có upload (Wasmer không chắc chạy scheduler).
- Stream, không RAM; Range giữ nguyên.

## Cổng 4 — Dev

DiskWatermark, PacketQuota, MediaController, Packet::fillFromRequest, config/env.

## Cổng 5 — Test

`tests/Feature/LegacyMediaTest.php` + `LegacyPacketTest` — pass (1GB gói, chunk vượt gói bị chặn)
