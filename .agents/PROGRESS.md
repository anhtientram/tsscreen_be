# Progress log

Cập nhật **ngay** khi xong một việc (endpoint, migration, phase, docs). Template: [`.agents/skills/note-progress/SKILL.md`](skills/note-progress/SKILL.md).

## Status

- **Current phase:** 6
- **Last completed:** phpix — không rewrite `/uploads/` (loop Wasmer)
- **Blocked:** —

## Phase checklist

- [x] 0 Nền (LegacyJson, routes, migrations `tb_*`, config, Swagger)
- [x] 1 Auth customer + admin
- [x] 2 Gói / đơn / hạn mức
- [x] 3 Dir + device pairing
- [x] 4 Campaign + lịch
- [x] 5 Media quota + chunk
- [ ] 6 Notify in-app
- [ ] 7 Lệnh + Firebase từ server (không poll 5s/10s)

## Đã ship (Phase 0–5)

- **Nền:** Laravel legacy routes, `LegacyJson` (app `text/html` / Swagger JSON), `GET /config6789.php`, Swagger `/api/documentation` tag 3 app, bảng `tb_*` + SQL hosting `database/sql/init_db_d26589bb.sql`.
- **Auth:** Customer `/home/register|login|OTP|reset|changepass|DeleteUser1|GetInfo|UpdateInfo`; TV `GetListCustomer_Bysericomputer`; Admin `/sysaccount/login` (MD5→bcrypt) + CRUD account. Pass DB = bcrypt, không plaintext/MD5 trần.
- **Gói/đơn:** Catalog + admin CRUD packet; phone mua/hủy/giao dịch/VietQR stub; admin OrderNew/active (`vaild_date`)/filter; `PacketQuota`.
- **Dir/TV:** CreateDir (msg=id_dir), share dir, on/off dir; CreateDevice (`seri_computer`, quota `limit_qty`); list Phone/TV/Admin; FCM token + ROM + heartbeat 60s.
- **Campaign:** Create/Approve + time run; TV `GetCampToday_ByComputerId` / `GetAllRunTimeOfComputer_4`; `url_youtobe`/`url_usp`; `GetCampaignRunProfile_Genaral`; map run profile qua `seri_computer`. Chưa gửi VIDEO_FROMCAMP.
- **Media:** `uploads/{customer_token}/`, path `./uploads/...`; chunk `_large`; quota `limit_capacity` từ `tb_resources`; disk 85%; Range serve; prune `.part*` 24h.
- **Seed:** admin `admin`/`admin123`, customer `customer@tsscreen.local`/`123456`, 3 gói.
- **Chưa:** notify in-app (Phase 6), FCM lệnh (Phase 7).

## Log

### 2026-08-21 — phpix rewrite loop trên file tĩnh

- **Done:** Log `phpix::server::htaccess rewrite loop` path `/uploads/...jpg`. Wasmer không nhận `!-f` rồi rewrite lặp cùng URL. **Không** `RewriteRule` nào khớp `/uploads/` (kể cả `-` / `index.php`). Xóa `public/uploads/.htaccess`. MIME bằng `AddType` ở `public/.htaccess`.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / —
- **Files:** `public/.htaccess`, xóa `public/uploads/.htaccess`
- **Next:** Deploy; HEAD jpg không còn loop, `Content-Type: image/jpeg`.
- **Notes:** Rule `^uploads/ index.php` và `^uploads/ -` đều loop trên phpix vì path không đổi / bị apply lại.

### 2026-08-21 — Rewrite loop `/uploads/`

- **Done:** Gỡ `RewriteRule ^uploads/ index.php` — Wasmer báo rewrite loop. File có sẵn thì serve static + `AddType` MIME; không có thì mới vào Laravel (`!-f`).
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / —
- **Files:** `public/.htaccess`
- **Next:** Deploy lại; HEAD `.jpg` phải `image/jpeg`, không còn loop.
- **Notes:** Ép mọi `/uploads/` vào `index.php` khi file đã tồn tại làm Apache/Wasmer rewrite lặp.

### 2026-08-21 — HEAD 200 nhưng app vẫn lỗi (Content-Type)

- **Done:** Phone/TV `isImageUrlValid` / `isVideoUrlValid` cần HEAD 200 **và** `Content-Type` bắt đầu `image/` hoặc `video/` — không `octet-stream`/`text/html`. HEAD trả MIME theo đuôi file (`.jpg`→`image/jpeg`, `.mp4`→`video/mp4`) + `Content-Length`. Rewrite `/uploads/` vào Laravel; AddType MIME. Admin không HEAD.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / test
- **Files:** `MediaController.php`, `public/.htaccess`, `public/uploads/.htaccess`, `tests/Feature/LegacyMediaTest.php`
- **Next:** Deploy Wasmer, HEAD lại file `.jpg` phải thấy `Content-Type: image/jpeg`. Phase 6 notify (`GetNofity*`) vẫn 404.
- **Notes:** `response('', 200)` làm Laravel ghi `text/html` + `Content-Length: 0`.

### 2026-08-21 — HEAD /uploads 404 trên Wasmer

- **Done:** App `HEAD` URL `./uploads/{token}/file` (lấy content-length). File trước đó chỉ nằm `storage/app/public` nên Wasmer 404. Ghi vào `public/uploads/`, route GET+HEAD, Content-Length.
- **Pipeline:** phân tích 3 app / DB / tối ưu / dev / test
- **Files:** `MediaController`, `routes/legacy.php`, `config/filesystems.php`, `tests/Feature/LegacyMediaTest.php`
- **Next:** Deploy hosting, **upload lại** ảnh (file cũ không tự copy). `public/uploads` phải ghi được.
- **Notes:** Notify `GetNofityNew_*` vẫn 404 (Phase 6).

### 2026-08-21 — Fix CreateCamp default_yn null trên hosting

- **Done:** App gửi `default_yn` / `run_by_default_yn` rỗng → MySQL `NOT NULL` 500. Chuẩn hóa thành `'0'`. CreateCamp lỗi DB trả `status -2` JSON, không HTML 500.
- **Pipeline:** phân tích 3 app / DB (không đổi schema) / tối ưu / dev / test
- **Files:** `app/Http/Controllers/Legacy/CampaignController.php`, `tests/Feature/LegacyCampaignTest.php`
- **Next:** Deploy hosting rồi thử lại CreateCamp
- **Notes:** Payload phone: `url_youtobe` + `url_yotobe`, `approved_yn=1`, `id_dir=1`.

### 2026-08-21 — Login customer bằng SĐT

- **Done:** `/home/login` tìm `email` hoặc `phone_number`. App Phone label «Email / Số điện thoại» vẫn POST field `email`.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / test
- **Files:** `app/Http/Controllers/Legacy/HomeAuthController.php`, `tests/Feature/LegacyAuthTest.php`
- **Next:** Phase 6 notify
- **Notes:** Seed: `0900000000` + `123456` hoặc `customer@tsscreen.local` + `123456`.

### 2026-08-21 — SQL hosting seed Phase 0–5

- **Done:** Cập nhật `database/sql/seed.sql` + đoạn seed trong `init_db_d26589bb.sql`: config, admin, customer, 3 gói, dir Demo, đơn Gói cơ bản pay=1. Phase 3–5 không thêm cột.
- **Pipeline:** docs / DB / — / — / —
- **Files:** `database/sql/seed.sql`, `database/sql/init_db_d26589bb.sql`
- **Next:** Phase 6 notify
- **Notes:** Import xong phải UPDATE `API_SERVER`. Admin `admin`/`admin123`, phone `customer@tsscreen.local`/`123456`.

### 2026-08-21 — Config API không cache tb_configs

- **Done:** `GET /config6789.php` đọc thẳng `tb_configs`. Trước đó `Cache::remember` 60s + `CACHE_STORE=database` nên UPDATE SQL phpMyAdmin vẫn trả `API_SERVER` cũ.
- **Pipeline:** phân tích 3 app / DB / tối ưu (bảng ~20 dòng, không cache) / dev / test
- **Files:** `app/Models/AppConfig.php`
- **Next:** Phase 6 notify
- **Notes:** App local đọc DB `db_tsscreen`. Splash 3 app vẫn gọi host cũ `config6789.php` trước, rồi mới dùng `API_SERVER`.

### 2026-08-21 — Phase 3+4+5 dir, campaign, media

- **Done:** Pairing TV (CreateDir → CreateDevice quota), campaign lịch TV UTC+7, upload local + chunk + quota. Phân tích 3 app trước khi code (thẻ `.agents/modules/phase3-dir-device.md` … `phase5-media.md`). Test 11 Legacy pass.
- **Pipeline:** phân tích 3 app / DB (không thêm cột) / tối ưu (with, PacketQuota server, stream, không poll) / dev / test
- **Files:** `DirController`, `DeviceController`, `CampaignController`, `MediaController`, `routes/legacy.php`, `tests/Feature/LegacyDirDeviceTest.php`, `LegacyCampaignTest.php`, `LegacyMediaTest.php`
- **Next:** Phase 6 — notify in-app (`Nofity`, InsertNotify)
- **Notes:** CreateDevice từ chối khi hết `limit_qty`. TV `created_by` numeric. GetAllCamp_ById dùng `camp_list`. Upload `status` 1; `file_size` int. Không FCM lệnh.

### 2026-08-21 — Snapshot Phase 0–2 trên PROGRESS

- **Done:** Ghi rõ đã ship nền/auth/gói; current phase = 3 chưa làm dir/device.
- **Pipeline:** docs
- **Files:** `.agents/PROGRESS.md`
- **Next:** Phase 3 — dir + pairing TV (5 cổng / cụm API)

### 2026-08-21 — Workflows xây dựng: 5 cổng mỗi module/API

- **Done:** Viết lại cách làm: mọi API phải qua phân tích 3 app → database đúng cột cần → tối ưu → dev → test. Skill `build-module`. Phase 0–7 chuyển vào `workflows/phases.md`.
- **Pipeline:** docs (không phải một endpoint)
- **Files:** `.agents/workflows.md`, `.agents/workflows/*.md`, `.agents/templates/module-card.md`, `.agents/skills/build-module/`, `AGENTS.md`, rules `06-pipeline`
- **Next:** Phase 3 dir/device — mỗi cụm API đi đủ 5 cổng
- **Notes:** Không code trước khi xong cổng 1–3. Thiếu test = chưa xong.

### 2026-08-21 — SQL schema + seed cho hosting `db_d26589bb`

- **Done:** File import phpMyAdmin: `database/sql/init_db_d26589bb.sql` (`USE db_d26589bb`, tạo bảng Laravel + `tb_*`, seed config/admin/customer/3 gói). Nhớ sửa `API_SERVER` thành URL public.
- **Files:** `database/sql/init_db_d26589bb.sql`, `database/sql/seed.sql`, `database/schema/mysql-schema.sql`
- **Next:** Phase 3 — dir + pairing TV
- **Notes:** Admin `admin`/`admin123`. Customer `customer@tsscreen.local`/`123456`. Password trong SQL là bcrypt, không plaintext.

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
