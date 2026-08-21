# Cổng 5 — Test

Không pass test = chưa xong module.

## Bắt buộc

1. Feature test theo thẻ cổng 1: path, FormData, `status`, list key, ID kiểu string, typo field.
2. `php artisan test --filter=<TestClass>`.
3. Swagger: tag đúng app; Try it out (Referer `/api/documentation`) ra `application/json`.

## Nên có

- Case lỗi `-2` + `msg` nếu app hiện toast.
- Quota: hết hạn mức thì reject.
- App thứ 2/3 cùng path (nếu cổng 1 ghi TV + phone).

Seed test: `AuthSeeder` / `PacketSeeder` hoặc factory tối thiểu trong test.
