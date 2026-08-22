# Lệnh điều khiển (Phase 7)

User chọn **API cũ**: Laravel insert/select/update `tb_commands` thôi. **Không** FCM từ server, **không** RTDB.

TV native GET `GetNewCommands_BySeriComputer` mỗi 10s khi FCM APK fail. Phone GET `GetInfoCommand_ByID` mỗi 1s khi chờ reply.

- CreateCommand: lưu `done=0` `sync=0`; trả `cmd_id` string. Không gửi FCM.
- GetNewCommands: pending `done=0` + `sync` rỗng/0; claim `sync=1` (tránh chạy lại mỗi 10s). Không rate-limit 30s (sẽ gãy poll 10s).
- GetInfoCommand_ByID: `cmd_list`; datetime luôn parse được; `second_wait` string.
- ReplyCommand: `return_value`, `done=1`.

Heartbeat 60s `UpdateAliveTimeDevice_ById` giữ — không dùng để lấy lệnh.
