# Core

- Chỉ làm **phase hiện tại** trong `PROGRESS.md`. Phase 7 lệnh = API DB; Laravel không FCM.
- **Mọi module/API:** đủ 5 cổng (`workflows.md`) — phân tích 3 app → DB → tối ưu → dev → test. Không code trước cổng 1–3. Không skip test.
- 3 app Flutter: giữ nguyên path/payload đến khi chủ động sửa (Phase 7 tắt poll).
- Bảng prefix `tb_`. ID integer, JSON trả **string**.
- Response: app = chuỗi JSON `text/html` (không sửa Flutter). Swagger (Referer `/api/documentation`) = `application/json` pretty.
- `status`: `1` ok, `-2` hiện `msg`, `-1` OTP lỗi. List rỗng vẫn có key `[]`.
- Giữ typo API: `Nofity`, `url_youtobe`, `url_usp`, `descript`, `vaild_date`, `seri_computer`, `Genaral`.
- POST = multipart FormData. Route không CSRF, không prefix `/api`.
- Giờ API (`now()`, `commit_time`, `created_date`, …): **Asia/Ho_Chi_Minh (UTC+7)**.
- Hosting (Wasmer/VPS/shared): rule `05-hosting.md` — quota gói ≠ dung lượng ổ; log `GET /logs/app?key=`.
- Mỗi việc xong: ghi `.agents/PROGRESS.md`.
