# Cổng 2 — Database chuẩn theo thứ app cần

Mục tiêu: schema **đủ và đúng**, không thừa cột không ai đọc.

## Quy tắc

- Prefix `tb_*`. PK `*_id` integer auto increment. JSON API trả **string**.
- Cột = field `fromJson` / FormData / chỗ app ghi.
- Index: cột dùng trong `where` của endpoint này (`customer_id`, `seri_computer`, `email`, `paid_id`, `sn`…).
- Soft delete: `deleted` `'y'`/`'n'` nếu app lọc.
- Timestamp tên cũ nếu app đọc: `created_date`, `last_MDF_date`.

## Checklist

1. Đối chiếu thẻ cổng 1 với [rules/02-schema.md](../rules/02-schema.md) và migration hiện có.
2. Thiếu cột → migration mới; đồng bộ `database/sql/init_db_d26589bb.sql` + `database/schema/mysql-schema.sql` nếu dump đang dùng cho hosting.
3. Seed chỉ data demo cần để test cổng 5 (không seed rác).
4. Không tạo bảng mới nếu đã có `tb_*` tương đương.

## Xong khi

Liệt kê trên thẻ: bảng, cột thêm/sửa, index — hoặc “không đổi DB”.
