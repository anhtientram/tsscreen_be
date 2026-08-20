---
name: implement-phase
description: Implement the current TS Screen Laravel backend phase (0-7) matching Flutter legacy APIs. Use when coding a phase, adding endpoints, migrations, or Swagger for this rebuild.
---

# Implement phase

1. Đọc `.agents/PROGRESS.md` — chỉ làm **Current phase**.
2. Đọc section phase đó trong `.agents/workflows.md`.
3. Đọc rules: `00-core`, `01-legacy-api`, `02-schema`; thêm `03-media` nếu Phase 5, `04-commands` nếu Phase 7.
4. Dùng skill `legacy-api` khi viết controller/response.
5. Swagger: document endpoint vừa thêm.
6. Không sửa 3 app Flutter trừ khi user yêu cầu (Phase 7 tắt poll).
7. Xong: skill `note-progress`.

## Phase gate

- 0 → 1: LegacyJson + routes + config + Swagger trống chạy được
- 1: login/register customer + admin
- 2: packet/order + enforce hạn mức khi tạo device (device API có thể Phase 3)
- 3: dir + CreateDevice
- 4: campaign schedule TV
- 5: upload quota
- 6: notify
- 7: FCM từ server — cần user cung cấp key; không poll 5s/10s
