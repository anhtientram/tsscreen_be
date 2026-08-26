# Hosting (mọi nhà cung cấp)

Áp dụng Wasmer, VPS, shared, Docker — **không** chỉ một host.

- **Quota gói** (`limit_capacity`) = trần từng khách. 1GB gói ≠ 1GB ổ cứng.
- **Ổ đầy 100% = sập** (PHP/MySQL/log không ghi được). Đã xảy ra khi up hình/video cho đến hết 50GB.
- **Không up tới trần ổ.** Luôn chừa chỗ cho hệ thống (`UPLOADS_RESERVE_BYTES`, mặc định 64MB) **và** đặt trần file media `UPLOADS_VOLUME_CAP` **thấp hơn** dung lượng ổ.
- Ví dụ ổ **50GB**: `UPLOADS_VOLUME_CAP=48318382080` (~45GB). Khách up tới ~45GB media thì API trả *hosting đầy*, ổ còn ~5GB cho OS/DB/log — không sập như lần full 50GB.
- Nếu ổ nhỏ hơn **tổng** các gói đã bán (vd 10 khách × 10GB mà ổ 50GB): vẫn set CAP theo **ổ**, không theo tổng gói. Khách hết chỗ hosting thì báo, không ghi thêm.
- Upload stream, không ffmpeg; chunk lớn ước lượng trước khi ghi.
- Log lỗi: `App\Support\AppLog` → `storage/logs/app/`. Xem: `GET {APP_URL}/logs/app?key={LOG_VIEWER_KEY}`.
