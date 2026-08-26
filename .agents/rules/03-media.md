# Media (Phase 5)

- Path JSON: `./uploads/{customer_token}/file.mp4` — app `replaceFirst('.', hostApi)`. Disk: `public/` local, Wasmer `UPLOADS_ROOT=/data` (volume `/data/uploads`).
- Stream upload, không load cả file vào RAM.
- >200MB: chunk 100MB `uploadfile_customer_large`. `cancelUpload` xóa `.part*`.
- Quota gói: `limit_capacity` **bytes** (1 = 1GB nếu admin nhập 1–1024). `used + file <= gói`. File trùng tên ghi đè, không cộng dồn.
- Chunk: ước lượng `total_chunks * size` trước khi ghi `.part`.
- Hosting: **mọi nhà cung cấp** — disk/volume uploads > tổng gói + reserve 64MB. `UPLOADS_VOLUME_CAP` nếu ổ nhỏ hơn tổng gói. Xem `.agents/rules/05-hosting.md`.
- Max 2 upload / user và 2 / instance; max ~110MB / request.
- MIME: jpeg/png/webp/gif, mp4/mov/webm. Chặn php/html.
- HTTP Range cho video. Không ffmpeg lúc upload.
- Prune `.part*` > 24h (scheduler + opportunistic khi upload).
