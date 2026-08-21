# Module: Media upload quota + chunk

Phase: 5

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | POST checkdir_customer, createdir_customer, getfiles_customer, getsizeofdir_customer, uploadfile_customer, uploadfile_customer_large, deletefile_customer, cancelUpload | name_dir = customer_token; fileupload; large: filename, chunk_index, total_chunks, customer_id; delete name_file | file_list; totalsize string; path_file object; status 1 hoặc true | Có |
| TV | Không (chỉ HEAD/GET URL video) | Serve `./uploads/{token}/file` → host + `/uploads/...` | HTTP Range seek | file bytes | — |
| Admin | Không | — | — | — | — |

Typo / exception: path `./uploads/{token}/file`; file_size **int** (json num); skip `.part*`; status true **or** 1.

HEAD `/uploads/...`: Phone `isImageUrlValid` / `isVideoUrlValid` cần HTTP 200 **và** `Content-Type` bắt đầu `image/` hoặc `video/` (không `octet-stream` / `text/html`). `content-length` > 0.

## Cổng 2 — Database

Bảng: `tb_resources`, `tb_upload_chunks`  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- Stream UploadedFile, không load RAM.
- Quota `tb_resources.file_size` + PacketQuota limit_capacity (server).
- Disk > 85% reject (skip trong testing **và** disk ảo < 4GB — Wasmer).
- Max 2 upload/user; max ~110MB/request.
- MIME whitelist; queue prune `.part*` > 24h.
- Serve Range via BinaryFileResponse.

## Cổng 4 — Dev

MediaController, PruneUploadParts command, public uploads route

## Cổng 5 — Test

`tests/Feature/LegacyMediaTest.php` — pass
