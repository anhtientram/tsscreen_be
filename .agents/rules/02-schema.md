# Schema `tb_*`

| Bảng | Dùng |
|------|------|
| `tb_users` | Customer phone/TV (`customer_token` = folder media) |
| `tb_accounts` | Admin |
| `tb_otp_codes` | `code_authen` |
| `tb_packets` | `limit_qty` thiết bị, `limit_capacity` bytes |
| `tb_orders` | Đơn mua/gia hạn |
| `tb_transactions` | Thanh toán |
| `tb_dirs` / `tb_dir_shares` | Nhóm thiết bị (không phải folder file) |
| `tb_devices` / `tb_device_shares` | TV, FCM `computer_token`, ROM, serial |
| `tb_campaigns` / `tb_campaign_time_runs` / `tb_campaign_run_profiles` | Chiến dịch + thống kê |
| `tb_notifications` / `tb_account_notifications` | In-app |
| `tb_resources` / `tb_upload_chunks` | Metadata file + chunk |
| `tb_commands` | Lệnh — logic FCM Phase 7 |
| `tb_configs` | `config6789.php` |

Gói còn hạn: `pay=1`, `deleted != y`, `valid_date < now < expire_date`. Enforce `limit_qty` khi tạo device, `limit_capacity` khi upload.
