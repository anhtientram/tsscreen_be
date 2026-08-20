# Core

- Chỉ làm **phase hiện tại** trong `PROGRESS.md`. Phase 7 (FCM lệnh) sau khi xong 0–6.
- 3 app Flutter: giữ nguyên path/payload đến khi chủ động sửa (Phase 7 tắt poll).
- Bảng prefix `tb_`. ID integer, JSON trả **string**.
- Response: app = chuỗi JSON `text/html` (không sửa Flutter). Swagger (Referer `/api/documentation`) = `application/json` pretty.
- `status`: `1` ok, `-2` hiện `msg`, `-1` OTP lỗi. List rỗng vẫn có key `[]`.
- Giữ typo API: `Nofity`, `url_youtobe`, `url_usp`, `descript`, `vaild_date`, `seri_computer`, `Genaral`.
- POST = multipart FormData. Route không CSRF, không prefix `/api`.
- Mỗi việc xong: ghi `.agents/PROGRESS.md`.
