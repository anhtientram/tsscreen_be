# Media (Phase 5)

- Path JSON: `./uploads/{customer_token}/file.mp4` — app `replaceFirst('.', hostApi)`.
- Stream upload, không load cả file vào RAM.
- >200MB: chunk 100MB `uploadfile_customer_large`. `cancelUpload` xóa `.part*`.
- Quota từ `tb_resources`, không scan disk mỗi request.
- Disk > 85% → reject upload.
- Max 2 upload đồng thời / user; max 110MB / request.
- MIME: jpeg/png/webp/gif, mp4/mov/webm. Chặn php/html.
- HTTP Range cho video. Không ffmpeg lúc upload.
- Queue xóa chunk dở > 24h.
