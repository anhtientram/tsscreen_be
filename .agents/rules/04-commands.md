# Lệnh điều khiển (Phase 7)

**Không poll 5s/10s.** TV cũ GET 10s khi FCM fail; phone GET 1s chờ reply — dễ sập khi nhiều máy + upload.

Hướng đúng: Laravel queue FCM tới `computer_token`. Service account **chỉ trên server**.

Phase 0–6: migration `tb_commands` được, **không** gửi FCM, **không** khuyến khích poll.

Khi làm Phase 7, nếu còn endpoint cũ:

- `GetNewCommands_BySeriComputer`: rate-limit ≥ 30s / device
- `GetInfoCommand_ByID`: rate-limit ≥ 3s / user

Heartbeat 60s `UpdateAliveTimeDevice_ById` giữ — không dùng để lấy lệnh.
