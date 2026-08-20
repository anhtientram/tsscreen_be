# Progress log

Cập nhật **ngay** khi xong một việc (endpoint, migration, phase, docs). Template: [`.agents/skills/note-progress/SKILL.md`](skills/note-progress/SKILL.md).

## Status

- **Current phase:** 3
- **Last completed:** Swagger chia tag theo 3 app (Customer / Projector / Admin)
- **Blocked:** —

## Phase checklist

- [x] 0 Nền (LegacyJson, routes, migrations `tb_*`, config, Swagger)
- [x] 1 Auth customer + admin
- [x] 2 Gói / đơn / hạn mức
- [ ] 3 Dir + device pairing
- [ ] 4 Campaign + lịch
- [ ] 5 Media quota + chunk
- [ ] 6 Notify in-app
- [ ] 7 Lệnh + Firebase từ server (không poll 5s/10s)

## Log

### 2026-08-20 — Swagger tag theo 3 app

- **Done:** Gom Swagger thành `Customer (Phone)`, `Projector (TV)`, `Admin`. Route dùng chung (login, config, GetAllPacket, GetPacket_ByCustomerId, sysaccount/login TV, GetListAccount…) gắn nhiều tag. Hằng `App\OpenApi\AppTags`. UI mặc định expand list tag.
- **Files:** `app/OpenApi/AppTags.php`, `app/OpenApi/OpenApiSpec.php`, controllers Legacy, `config/l5-swagger.php`, rules/skills swagger
- **Next:** Phase 3 — dir + pairing TV
- **Notes:** Reload `/api/documentation`. Ô filter gõ Customer / Projector / Admin.

### 2026-08-20 — Phase 2: packet / đơn / hạn mức

- **Done:** Catalog + admin CRUD gói (`CreatePacket`, `UpdatePacket_ById`, `DeletePacket_ById`). Phone mua/gia hạn, đơn chờ, admin kích hoạt (`vaild_date`), filter, VietQR stub, danh sách khách. `PacketQuota` sẵn cho Phase 3 (limit_qty TV, limit_capacity bytes). Seed 3 gói demo. Test `LegacyPacketTest` pass.
- **Files:** `app/Http/Controllers/Legacy/{Packet,Order,SysAccountOrder,CustomerAdmin,VietQr}Controller.php`, `app/Services/PacketQuota.php`, `app/Models/{Packet,Order}.php`, `routes/legacy.php`, `database/seeders/PacketSeeder.php`, `tests/Feature/LegacyPacketTest.php`
- **Next:** Phase 3 — dir + pairing TV (`CreateDevice`, shares, ROM, FCM token, heartbeat)
- **Notes:** Admin app có thêm CRUD gói, không chỉ xem catalog như phone. `detail` luôn string (admin `.trim()`). VietQR chưa webhook — cấu hình `VIETQR_BANK_BIN` / `VIETQR_ACCOUNT` trong `tb_configs` để ra ảnh QR; không có thì `qrLink` trỏ trang HTML nội bộ. InsertNotify lúc mua = Phase 6.

### 2026-08-20 — App text/html, Swagger JSON

- **Done:** `LegacyJson` nhận diện Referer `/api/documentation`: Swagger Try it out = `application/json` pretty. App Flutter = chuỗi JSON `text/html` như PHP cũ, không cần sửa `jsonDecode`.
- **Files:** `app/Support/LegacyJson.php`, tests, rules/skills/workflows
- **Next:** Phase 2 — packet / đơn

### 2026-08-20 — Fix PHP 8.3 / Herd

- **Done:** Composer đã kéo Symfony 8 (cần PHP 8.4) vì máy cài package dùng 8.4. Khóa `config.platform.php = 8.3.32`, downgrade Symfony 8 → 7.4 để Herd PHP 8.3.32 chạy được.
- **Files:** `composer.json`, `composer.lock`
- **Next:** Phase 2 — packet / đơn
- **Notes:** Reload site Herd, không cần đổi PHP.

### 2026-08-20 — Response application/json

- **Done:** Đổi `LegacyJson` từ `text/html` sang `application/json` + pretty-print để Swagger/browser không còn khối JSON dính một dòng.
- **Files:** `app/Support/LegacyJson.php`, OA Config, rules/skills/workflows, `tests/Feature/LegacyAuthTest.php`
- **Next:** Phase 2 — packet / đơn
- **Notes:** App Flutter đang `jsonDecode(response.data)`. Dio parse sẵn khi Content-Type JSON — nếu login app lỗi, dùng `response.data` (Map) thay vì `jsonDecode`.

### 2026-08-20 — Phase 0 + 1: schema, config, auth, Swagger

- **Done:** Tạo đủ bảng `tb_*` từ model 3 app. `LegacyJson` (JSON string + text/html). `GET /config6789.php`. Auth `/home/*` + `/sysaccount/*` + OTP. l5-swagger `/api/documentation`. Seed admin `admin` / `admin123` (app gửi MD5), customer `customer@tsscreen.local` / `123456`. Test `LegacyAuthTest` pass.
- **Files:** `database/migrations/2026_08_20_000001_create_tb_tables.php`, `app/Support/*`, `app/Models/*`, `app/Http/Controllers/Legacy/*`, `routes/legacy.php`, `app/OpenApi/OpenApiSpec.php`, `database/seeders/*`, `tests/Feature/LegacyAuthTest.php`
- **Next:** Phase 2 — packet CRUD, mua/gia hạn, kích hoạt đơn, VietQR stub, enforce `limit_qty`/`limit_capacity`
- **Notes:** Swagger UI: `http://localhost:8000/api/documentation`. Password JSON login echo lại plaintext client gửi, không trả bcrypt. Google login password rỗng được chấp nhận (giống API cũ). `tb_commands` đã có bảng, chưa có FCM.

### 2026-08-20 — scaffold agent kit

- **Done:** Tạo `.agents` (workflows, rules, skills), `AGENTS.md`, `PROGRESS.md`. Chuyển workflows từ `docs/`.
- **Files:** `AGENTS.md`, `.agents/**`, `.cursor/rules/tsscreen.mdc`, `.cursor/skills/**`, `docs/workflows.md`
- **Next:** Phase 0 — LegacyJson, `routes/legacy.php`, migrations `tb_*`, `config6789.php`, Swagger
- **Notes:** Lệnh remote = Phase 7. Không implement poll 5s/10s. Password: customer plaintext, admin MD5, Google password rỗng.
