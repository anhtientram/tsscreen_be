---
name: implement-phase
description: Implement the current TS Screen Laravel backend phase (0-7) matching Flutter legacy APIs. Use when coding a phase, adding endpoints, migrations, or Swagger for this rebuild.
---

# Implement phase

1. Đọc `.agents/PROGRESS.md` — chỉ làm **Current phase**.
2. Domain phase: [`.agents/workflows/phases.md`](../../workflows/phases.md).
3. **Mỗi API/module trong phase:** skill `build-module` — đủ 5 cổng (phân tích 3 app → DB → tối ưu → dev → test). Không skip.
4. Rules: `00-core`, `01-legacy-api`, `02-schema`, `06-pipeline`; thêm `03-media` nếu Phase 5, `04-commands` nếu Phase 7.
5. Skill `legacy-api` khi viết controller. Swagger `AppTags`.
6. Không sửa 3 app Flutter trừ khi user yêu cầu (Phase 7 tắt poll).
7. Xong: skill `note-progress` (ghi đã qua 5 cổng).

## Phase gate

- 0 → 1: LegacyJson + routes + config + Swagger trống chạy được
- 1: login/register customer + admin
- 2: packet/order + enforce hạn mức khi tạo device (device API có thể Phase 3)
- 3: dir + CreateDevice
- 4: campaign schedule TV
- 5: upload quota
- 6: notify
- 7: lệnh API cũ (Create/Get/Reply DB). Laravel **không** FCM.
