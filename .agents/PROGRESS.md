# Progress log

Cập nhật **ngay** khi xong một việc (endpoint, migration, phase, docs). Template: [`.agents/skills/note-progress/SKILL.md`](skills/note-progress/SKILL.md).

## Status

- **Current phase:** —
- **Last completed:** CreateCamp resolve customer từ id_dir khi customer_id rỗng
- **Blocked:** —

## Phase checklist

- [x] 0 Nền (LegacyJson, routes, migrations `tb_*`, config, Swagger)
- [x] 1 Auth customer + admin
- [x] 2 Gói / đơn / hạn mức
- [x] 3 Dir + device pairing
- [x] 4 Campaign + lịch
- [x] 5 Media quota + chunk
- [x] 6 Notify in-app
- [x] 7 Lệnh API cũ (Create/Get/Reply DB; Laravel không FCM; TV tự GET)

## Đã ship (Phase 0–7)

- **Nền:** Laravel legacy routes, `LegacyJson` (app `text/html` / Swagger JSON), `GET /config6789.php`, Swagger `/api/documentation` tag 3 app, bảng `tb_*` + SQL hosting `database/sql/init_db_d26589bb.sql`. Timezone **Asia/Ho_Chi_Minh (UTC+7)**.
- **Auth:** Customer `/home/register|login|OTP|reset|changepass|DeleteUser1|GetInfo|UpdateInfo`; TV `GetListCustomer_Bysericomputer`; Admin `/sysaccount/login` (MD5→bcrypt) + CRUD account. Pass DB = bcrypt, không plaintext/MD5 trần.
- **Gói/đơn:** Catalog + admin CRUD packet; phone mua/hủy/giao dịch/VietQR stub; admin OrderNew/active (`vaild_date`)/filter; `PacketQuota`.
- **Dir/TV:** CreateDir (msg=id_dir), share dir, on/off dir; CreateDevice (`seri_computer`, quota `limit_qty`); list Phone/TV/Admin; FCM token + ROM + heartbeat 60s.
- **Campaign:** Create/Approve + time run; TV `GetCampToday_ByComputerId` / `GetAllRunTimeOfComputer_4`; `url_youtobe`/`url_usp`; `GetCampaignRunProfile_Genaral`; map run profile qua `seri_computer`. **`id_computer` = video 1 TV; chỉ `id_dir` = cả hệ thống.** Chưa gửi VIDEO_FROMCAMP.
- **Media:** `uploads/{customer_token}/`; quota gói bytes (1–1024 = GB); **mọi hosting** disk/volume > tổng gói + reserve 64MB; `UPLOADS_VOLUME_CAP` nếu ổ nhỏ hơn; 2 upload/instance; Range; prune `.part*`.
- **Log:** `AppLog` → `storage/logs/app/app-YYYY-MM-DD.log`. Trang `{APP_URL}/logs/app?key=LOG_VIEWER_KEY`.
- **Notify:** in-app DB only — `GetNofity_*` / `InsertNotify` / `InsertNotify_Account` / `UpdateNotify`; list `Nofity_list`; `descript`; `count` int; `seen` `'0'`/`'1'`. **Không FCM.**
- **Lệnh:** `CreateCommand` lưu `tb_commands`; TV `GetNewCommands_BySeriComputer` (claim `sync=1`); `ReplyCommand`; Phone `GetInfoCommand_ByID`. Laravel **không** đẩy FCM/RTDB. FCM nếu có là từ APK Phone/Admin.
- **Seed:** admin `admin`/`admin123`, customer `customer@tsscreen.local`/`123456`, 3 gói.
- **Không làm:** FCM notify in-app; FCM lệnh từ Laravel; Firebase Realtime.

## Log

### 2026-09-03 — CreateCamp resolve customer từ id_dir khi customer_id rỗng

- **Done:** Phone gửi `CreateCamp` với `customer_id` rỗng + `id_dir=1` → `status -2 Không tìm thấy khách hàng`. Endpoint đã có Phase 4 nhưng chỉ check `customer_id`. Thêm `LegacyCustomerResolver`: fallback `customer_token`/`name_dir`, `email`, `computer_id`/`id_computer`, `id_dir` → owner dir.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `LegacyCustomerResolver.php`, `CampaignController.php`, tests
- **Next:** Deploy hosting; thử tạo camp + AddTimeRun_ByCamp
- **Notes:** Request Flutter log khớp: `customer_id` rỗng, `id_dir: 1`, `id_computer: 0`.

### 2026-09-03 — Upload APK qua POST form (bỏ Livewire FileUpload)

- **Done:** Lỗi `The data.tvbox_apk.{uuid} failed to upload` vẫn còn trên Wasmer — Livewire temp upload không ổn với APK lớn. Thay Filament FileUpload bằng form POST `POST /admin/apk/upload` (auth admin), lưu trực tiếp qua PHP `UploadedFile`, tự cập nhật `APPTVBOX_UPDATE_URL`.
- **Pipeline:** phân tích 3 app / DB không đổi / tối ưu hosting / dev / test
- **Files:** `TvBoxApkUploadController.php`, `tvbox-apk-upload.blade.php`, `AppConfigForm.php`, `ManageAppConfig.php`, `routes/web.php`, tests
- **Next:** Deploy; chọn file → bấm **Upload APK** (nút riêng, không cần Lưu cấu hình)
- **Notes:** Giới hạn 110MB khớp `public/.user.ini`. Lưu cấu hình chỉ dùng cho các field config khác.

### 2026-09-03 — Fix FileUpload loading vô hạn khi đã có APK trên server

- **Done:** Mở trang config thấy `tvbox.apk` + "Waiting for size" / Loading mãi dù chưa chọn file — FilePond cố `fetch()` cả APK cũ (80MB+) để hiển thị. Fix: không pre-fill `tvbox_apk`; hiện APK đang host qua Placeholder + link tải; FileUpload chỉ dùng upload mới.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `AppConfigForm.php`, `ManageAppConfig.php`, `TvBoxApkStorage.php`, `FilamentAdminTest.php`
- **Next:** Deploy; refresh `/admin/config` — ô upload trống, APK cũ hiện ở dòng "APK đang host"
- **Notes:** Nguyên nhân là Filament FileUpload + FilePond không phù hợp hiển thị file lớn đã có sẵn.

### 2026-09-03 — Fix loading vô hạn khi upload APK TV Box

- **Done:** Spinner loading mãi sau khi chọn APK — Filament copy file sang volume ngay lập tức (80MB+) trong cùng request Livewire. Fix: `storeFiles(false)` (chỉ upload tạm, copy khi bấm Lưu), `previewable(false)`, temp disk `uploads` cùng volume Wasmer. `TvBoxApkStorage::storeTemporary()` copy cross-filesystem.
- **Pipeline:** phân tích 3 app / DB không đổi / tối ưu UX upload / dev / test
- **Files:** `AppConfigForm.php`, `ManageAppConfig.php`, `TvBoxApkStorage.php`, `config/livewire.php`, `AppServiceProvider.php`
- **Next:** Deploy; chọn APK → đợi upload xong → bấm **Lưu cấu hình**
- **Notes:** Upload tạm có thể mất 1–2 phút với APK lớn; sau đó phải bấm Lưu mới ghi vào `/data/uploads/apk/tvbox.apk`.

### 2026-09-03 — Sửa upload APK TV Box (Livewire 100MB + volume Wasmer)

- **Done:** Fix lỗi `The data.tvbox_apk.{uuid} failed to upload`. Tăng giới hạn Livewire temp upload lên 100MB (`config/livewire.php`). Bỏ `acceptedFileTypes` + `fetchFileInformation(false)` tránh reject MIME `.apk`. Lưu file vào `{UPLOADS_ROOT}/uploads/apk/tvbox.apk` (Wasmer volume `/data/uploads/apk/`), route `GET /apk/tvbox.apk` không đổi.
- **Pipeline:** phân tích 3 app / DB không đổi / tối ưu hosting volume / dev / test
- **Files:** `config/livewire.php`, `config/filesystems.php`, `AppConfigForm.php`, `TvBoxApkStorage.php`, `AppServiceProvider.php`, `.gitignore`, tests
- **Next:** Deploy lên hosting; thử upload APK thật trong `/admin/config`
- **Notes:** Hosting đã có `upload_max_filesize=110M`, `post_max_size=120M` trong `public/.user.ini`. APK > 12MB trước đó bị Livewire mặc định chặn.

### 2026-09-03 — Upload APK TV Box trong cấu hình admin

- **Done:** `/admin/config` → App TV Box: upload file `.apk` thay URL tay. Lưu `public/apk/tvbox.apk`, tự gán `APPTVBOX_UPDATE_URL` = `{API_SERVER}/apk/tvbox.apk`. Route `GET /apk/tvbox.apk` (MIME APK). Disk `releases`.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `TvBoxApkStorage.php`, `AppConfigForm.php`, `ManageAppConfig.php`, `config/filesystems.php`, `routes/web.php`, tests
- **Next:** —
- **Notes:** Upload mới ghi đè `tvbox.apk`. TV đọc link qua config6789.

### 2026-09-03 — Format tiền VN 20.000 toàn hệ thống

- **Done:** `LegacyJson::money()` / `parseMoney()` — hiển thị `20.000`, DB vẫn lưu `20000`. API: `Packet_list`, `Packet_list` đơn, `transaction_list`, admin order list. Input mua/tạo gói parse ngược. Filament bảng/infolist/form qua `MoneyFormat`. Trang VietQR HTML hiển thị có dấu chấm.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `LegacyJson.php`, `MoneyFormat.php`, models, controllers, Filament, `tests/Unit/LegacyJsonTest.php`
- **Next:** —
- **Notes:** App gửi `99.000` hoặc `99000` đều OK nhờ `parseMoney`.

### 2026-09-03 — Filament config6789 + UI chi tiết + dashboard

- **Done:** Trang `/admin/config` sửa toàn bộ key `config6789.php` (công ty, API_SERVER, app version 3 app, VietQR). Lưu qua `AppConfig::putMany` — app đọc ngay, không cache. Infolist xem chi tiết: Section + badge + tiếng Việt (đơn, khách, gói, TV, GD, admin). Dashboard stats: màu Indigo/Emerald/Rose, icon, mini chart 7 ngày. View đơn có nút Kích hoạt.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `ManageAppConfig.php`, `AppConfigForm.php`, infolists, `StatsOverview.php`, `AdminPanelProvider.php`, `FilamentAdminTest.php`
- **Next:** —
- **Notes:** Nút "Xem config6789" mở JSON live.

### 2026-09-03 — Filament admin panel `/admin`

- **Done:** Cài Filament 4. Auth `tb_accounts` guard `admin` (login username + MD5 như API cũ). Resources: **Đơn hàng** (tab chờ/active/hết hạn + action Kích hoạt qua `OrderActivationService`), **Gói cước** CRUD, **Khách hàng** (bật/tắt), **Giao dịch**, **TV**, **Tài khoản admin**. Dashboard stats widget. Query Eloquent trực tiếp (không HTTP loop API). Legacy `/sysaccount/*` giữ nguyên; service kích hoạt dùng chung.
- **Pipeline:** phân tích 3 app / DB không đổi / query trực tiếp / dev / test
- **Files:** `composer.json`, `config/auth.php`, `app/Models/Account.php`, `app/Services/OrderActivationService.php`, `app/Filament/**`, `app/Providers/Filament/AdminPanelProvider.php`, `tests/Feature/FilamentAdminTest.php`
- **Next:** Mở `{APP_URL}/admin` — login `admin` / `admin123`
- **Notes:** App admin APK vẫn dùng `/sysaccount`. Filament là panel web bổ sung.

### 2026-09-03 — Gói dùng thử auto-active + ngày hạn

- **Done:** `BuyPacket_ByIdCustomer_1` với `is_trial=1` tự kích hoạt (`pay=1`), không chờ admin. `payment_date` và `valid_date` = `register_date`; `expire_date` = register + `day_qty` gói (admin cấu hình, mặc định 7 ngày). Tạo transaction. `GetPacket_ByCustomerId` trả đủ 3 field ngày.
- **Pipeline:** phân tích 3 app / DB không đổi / — / dev / test
- **Files:** `OrderController.php`, `tests/Feature/LegacyPacketTest.php`
- **Next:** —
- **Notes:** Gói thường vẫn `pay=0` chờ admin `active_order_1`.

### 2026-08-27 — Review tối ưu toàn hệ thống

- **Done:** Rà Phase 0–7 + cổng 3. Kết luận: đủ cho hosting nhỏ–vừa; không thiếu chức năng. Đã tốt: PacketQuota, DiskWatermark + VOLUME_CAP, stream/chunk/2 upload, with() list Phone/TV, lịch TV 2 query, lệnh claim sync không rate-limit 30s, index lookup chính. Còn: GetListCustomer N+1 devices; run profile GROUP BY + prune; index `id_computer` và `(sn, done, sync)`; GetCampToday preload default; hostingDeny SUM cache; heartbeat 1 UPDATE.
- **Pipeline:** phân tích 3 app (không đổi contract) / DB chưa đổi / tối ưu (audit) / — / —
- **Files:** controllers Legacy, `PacketQuota`, `DiskWatermark`, migration `tb_*`, tests Feature
- **Next:** Nếu làm tiếp: eager-load GetListCustomer → index → prune run profile/commands. Host 50GB giữ `UPLOADS_VOLUME_CAP` ~45GB.
- **Notes:** Không cache `tb_configs`. Không FCM Laravel. Không rate-limit poll lệnh 10s. Cron Wasmer có thể không chạy — prune phải opportunistic.

### 2026-08-26 — CreateCamp id_computer = video riêng 1 TV

- **Done:** Phone gửi `id_computer` (thường không gửi `computer_id`) khi thêm video cho 1 máy. Lưu cả `computer_id` và `id_computer`. List/lịch TV: camp của máy đó, hoặc camp cả hệ thống (`id_dir` + cả hai cột máy rỗng). Camp gắn TV A không hiện trên TV B cùng dir.
- **Pipeline:** phân tích 3 app / DB không đổi / tối ưu filter 1 query / dev / test
- **Files:** `CampaignController.php`, `.agents/modules/phase4-campaign.md`, `tests/Feature/LegacyCampaignTest.php`
- **Next:** Deploy; Phone thêm video 1 máy truyền `id_computer`.
- **Notes:** Camp chỉ `id_dir` (không `id_computer`) vẫn chạy mọi TV trong hệ thống.

### 2026-08-26 — Ổ 50GB đầy là sập: đặt VOLUME_CAP ~45GB

- **Done:** Ghi rule hosting: full 50GB (hình/video) làm PHP/DB không ghi được. Ổ 50GB → `UPLOADS_VOLUME_CAP=48318382080` (~45GB), từ chối upload trước khi chạm trần.
- **Pipeline:** — / — / tối ưu / docs / —
- **Files:** `.agents/rules/05-hosting.md`, `.env.example`
- **Next:** Trên host 50GB set env đó, không để CAP=0 nếu ổ có hạn.
- **Notes:** Không cần unlimit; cần không bao giờ để ổ 100%.

### 2026-08-26 — AppLog + /logs/app; hosting ghi chú mọi NCC

- **Done:** Rule `05-hosting.md`: quota gói ≠ ổ cứng, mọi hosting. Helper `AppLog` JSONL `storage/logs/app/`. Exception 500 ghi log. Trang HTML `GET /logs/app?key=`. Upload lỗi đi AppLog. Media dùng AppLog.
- **Pipeline:** phân tích 3 app (không gọi) / DB không đổi / tối ưu (400KB cuối file, redact secret) / dev / test
- **Files:** `AppLog.php`, `LogViewerController.php`, `resources/views/logs/app.blade.php`, `routes/web.php`, `bootstrap/app.php`, `.agents/rules/05-hosting.md`, `tests/Feature/AppLogViewerTest.php`
- **Next:** Set `LOG_VIEWER_KEY` trên hosting (dashboard, không commit). Mở `{APP_URL}/logs/app?key=...`.
- **Notes:** 8 tests liên quan pass. Key tối thiểu 8 ký tự.

### 2026-08-26 — Tối ưu media: quota GB + không sập volume

- **Done:** Gói 1GB = 1GiB upload (admin nhập `1`–`1024` = GB). Chặn trước khi ghi: quota gói, ước lượng chunk lớn, free space volume uploads (bỏ skip disk ảo Wasmer). Trần `UPLOADS_VOLUME_CAP`. `msg` rõ khi hết gói/hosting. 2 upload/instance. Ghép chunk xóa từng `.part`.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu disk+quota / dev / test
- **Files:** `PacketQuota.php`, `DiskWatermark.php`, `MediaController.php`, `Packet.php`, `config/filesystems.php`, `app.yaml`, `.agents/modules/media-quota-host.md`, tests
- **Next:** Deploy. Volume nhỏ hơn tổng gói thì set `UPLOADS_VOLUME_CAP` (bytes).
- **Notes:** Legacy 23 tests pass. Phone vẫn chỉ đọc `totalsize`; thêm `limit`/`remain` không phá app.

### 2026-08-22 — Giờ API UTC+7

- **Done:** `APP_TIMEZONE=Asia/Ho_Chi_Minh` (config, `.env.example`, `app.yaml`). `LegacyJson::date` format Carbon theo VN. `commit_time` / `created_date` / heartbeat / `now()` không còn UTC.
- **Pipeline:** — / DB (không đổi) / — / dev / test
- **Files:** `config/app.php`, `app/Support/LegacyJson.php`, `app.yaml`, `.env.example`, `phpunit.xml`, `tests/Feature/LegacyCommandTest.php`
- **Next:** Deploy Wasmer (env `APP_TIMEZONE` trong yaml).
- **Notes:** Legacy tests 21 pass.

### 2026-08-22 — Phase 7 lệnh API cũ (không FCM Laravel)

- **Done:** Phục hồi contract Phone/TV/Admin: `CreateCommand` insert DB `cmd_id` string; TV poll `GetNewCommands_BySeriComputer` `cmd_list` pending; claim `sync=1`; `ReplyCommand` `return_value` `done=1`; Phone `GetInfoCommand_ByID` (datetime + `second_wait` string). **Laravel không FCM/RTDB.**
- **Pipeline:** phân tích 3 app / DB (không đổi `tb_commands`) / tối ưu (1 query pending + claim, không rate-limit 30s vì app 10s) / dev / test
- **Files:** `CommandController.php`, `DeviceCommand.php`, `routes/legacy.php`, `tests/Feature/LegacyCommandTest.php`, `.agents/modules/phase7-command.md`, `storage/api-docs/api-docs.json`
- **Next:** Deploy; TV FCM fail sẽ GET 10s lấy lệnh. Pause/CAMP trên Phone → CreateCommand → TV GetNew → Reply.
- **Notes:** 3 tests pass. User chọn hướng cũ, không Realtime, không server FCM.

### 2026-08-22 — Phase 6 notify in-app

- **Done:** API typo `Nofity`: list/count customer + admin, `GetNofity_ById`, `UpdateNotify` seen=1, `InsertNotify` (customer; Admin duyệt gói), `InsertNotify_Account` (inbox admin). TV chỉ InsertNotify. **Không FCM.** User: noti thường; FCM note Phase 7 lệnh.
- **Pipeline:** phân tích 3 app / DB (không đổi `tb_notifications` + `tb_account_notifications`) / tối ưu (1 query list/count) / dev / test
- **Files:** `NotifyController.php`, `routes/legacy.php`, models notify, `tests/Feature/LegacyNotifyTest.php`, `.agents/modules/phase6-notify.md`, `storage/api-docs/api-docs.json`
- **Next:** Phase 7 lệnh + FCM khi có key. Deploy Phase 6: login hết 404 `GetNofityNew_*`.
- **Notes:** 4 tests pass. `id_notify` string; `count` JSON number.

### 2026-08-22 — app.yaml không migrate/seed

- **Done:** Job cũ `command: after_deploy` là lệnh anybuild (chỉ migrate khi provider còn inject). YAML repo không định nghĩa lệnh đó nên không chạy. Đổi job tường minh: `php /app/artisan migrate --force` rồi `db:seed --force`. Seeders idempotent (admin/customer/gói). `APP_URL` Wasmer để ConfigSeeder ghi `API_SERVER`.
- **Pipeline:** — / DB (không đổi schema) / — / — / —
- **Files:** `app.yaml`
- **Next:** Push; log deploy phải thấy migrate. Không dùng `migrate:fresh` (xóa data).
- **Notes:** Seed SQL phpMyAdmin (`seed.sql`) khác `DatabaseSeeder` — dir Demo/đơn gói cơ bản chỉ trong SQL.

### 2026-08-21 — Volume có, ảnh GET 500

- **Done:** Upload ok rồi instance `ExitCode::27`. GET `/uploads/...jpg` **500** vì file trong `public/` (phpix static/rewrite). Chuyển volume mount `/data/uploads`, `UPLOADS_ROOT=/data`, Laravel serve + MIME. URL app vẫn `/uploads/{token}/file`.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / test
- **Files:** `config/filesystems.php`, `app.yaml`, `.env.example`
- **Next:** Push; dashboard env `UPLOADS_ROOT=/data`; upload lại. Tab Storage file nằm `token/file.jpg`.
- **Notes:** Log PHP 8.3.21 + files.photo.gallery = UI browse volume, không phải API Laravel.

### 2026-08-21 — Storage trống dù upload ok

- **Done:** Log `[media] upload ok` = file ghi disk **instance** (ephemeral). Tab Storage trống = **chưa có Volume**. Git/anybuild ghi đè yaml dashboard, mất `volumes`. Thêm `app.yaml` trong repo: volume `uploads` → `/app/public/uploads`.
- **Pipeline:** — / DB (không đổi) / — / — / —
- **Files:** `app.yaml`
- **Next:** Commit + push `app.yaml`. Tab Storage phải hiện `uploads`. Rồi upload lại ảnh (deploy này xóa file vừa up).
- **Notes:** URL ảnh **ngay lúc này** (chưa restart) phải mở được.

### 2026-08-21 — app.yaml Wasmer thiếu volumes

- **Done:** Config `anhtientram/tsscreen_be` (phpix Laravel) chưa có Volume. Thêm `volumes: name uploads, mount /app/public/uploads`.
- **Pipeline:** — / DB (không đổi) / — / — / —
- **Files:** —
- **Next:** Paste yaml trên dashboard → deploy → tab Storage hiện volume → upload lại ảnh.
- **Notes:** Không nhét `volumes` vào `annotations`.

### 2026-08-21 — Wasmer Storage: no storage configured

- **Done:** Dashboard Storage trống = chưa Volume. Upload ghi disk ephemeral, deploy/idle là mất. Volume tạo qua `app.yaml` rồi deploy, không phải nút trên tab trống. Mount: `/app/public/uploads` (phpix `document_root=/app/public`).
- **Pipeline:** phân tích 3 app / DB (không đổi) / — / — / —
- **Files:** —
- **Next:** Thêm volume trên Wasmer, deploy, upload lại ảnh. Tab Storage sẽ hiện volume.
- **Notes:** Docs: https://docs.wasmer.io/edge/guides/volumes/

### 2026-08-21 — Upload không thấy log trên Wasmer

- **Done:** Console Wasmer chỉ PHPix boot, không có Laravel. `LOG_STACK` mặc định `single,stderr`. Upload ghi `[media] upload ok|reject|404`. `DiskWatermark` bỏ chặn khi disk ảo < 4GB (Wasmer hay báo >85% đầy). Sau `put` nếu file không tồn tại → `status -2`.
- **Pipeline:** phân tích 3 app / DB (không đổi) / tối ưu / dev / test
- **Files:** `DiskWatermark.php`, `MediaController.php`, `config/logging.php`, `.env.example`, `public/.user.ini`
- **Next:** Deploy; Wasmer env `LOG_STACK=single,stderr`. Upload rồi tìm log `[media]`. File vẫn mất khi deploy nếu chưa gắn Volume.
- **Notes:** Log 22:24 và 22:26 = hai lần start PHP (~2 phút), disk ephemeral reset.

### 2026-08-21 — Ảnh demo 404 sau push htaccess

- **Done:** URL `.../uploads/01demo0.../image_picker_*.png` trả **404**. File upload nằm `public/uploads/` trên disk **ephemeral** Wasmer — deploy / `ExitCode::27` xóa hết. DB `tb_resources` còn path, byte không còn. Không phải Content-Type.
- **Pipeline:** phân tích 3 app / DB (không đổi) / — / — / —
- **Files:** —
- **Next:** Upload lại từ Phone. Lâu dài: [Wasmer Volume](https://docs.wasmer.io/edge/guides/volumes/) mount `public/uploads`.
- **Notes:** Token seed `01demo0customer0token0tsscreen`.

### 2026-08-21 — Push htaccess làm Swagger/config xoay

- **Done:** phpix không parse `AddType` + `RewriteCond %{REQUEST_URI} !^/uploads/` trên mọi rule → front controller Laravel không chạy, `/api/documentation` và `/config6789.php` treo. Revert `public/.htaccess` về bản Laravel gốc. MIME media vẫn do `MediaController` (đuôi file).
- **Pipeline:** — / DB (không đổi) / tối ưu / dev / —
- **Files:** `public/.htaccess`
- **Next:** Push htaccess gốc; mở config/Swagger lại. Ảnh `/uploads/` serve static nếu file có thật.
- **Notes:** Đừng thêm AddType / exclude REQUEST_URI trên Wasmer PHPix.

### 2026-08-21 — Swagger/config “xoay” trên Wasmer

- **Done:** [`config6789.php`](https://tsscreen-be.wasmer.app/config6789.php) vẫn trả JSON. Swagger UI xoay vì `L5_SWAGGER_GENERATE_ALWAYS=true` scan `app/` mỗi lần `/docs`, Wasmer 4 worker + cold start. Default + `.env.example` → `false` (dùng `storage/api-docs/api-docs.json`).
- **Pipeline:** — / DB (không đổi) / tối ưu / dev / —
- **Files:** `config/l5-swagger.php`, `.env.example`
- **Next:** Deploy; trên Wasmer set `L5_SWAGGER_GENERATE_ALWAYS=false` nếu env cũ còn `true`. Đợi cold start 15–30s sau idle.
- **Notes:** `ExitCode::27` = Edge tắt instance idle, request đầu chậm.

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
