---
name: build-module
description: Build one TS Screen module or API through analyze (3 apps) → database → optimize → dev → test. Use whenever adding or changing a legacy endpoint, table, or phase feature.
---

# Build module (5 cổng bắt buộc)

Đọc [`.agents/workflows.md`](../../workflows.md). **Không skip cổng.** Không code trước khi xong 1–3.

1. **Phân tích 3 app** — [workflows/01-analyze.md](../../workflows/01-analyze.md). Điền [templates/module-card.md](../../templates/module-card.md).
2. **Database** — [workflows/02-database.md](../../workflows/02-database.md). Chỉ cột app cần; `tb_*`; cập nhật SQL hosting nếu đổi schema.
3. **Tối ưu** — [workflows/03-optimize.md](../../workflows/03-optimize.md). Quota server, không N+1, không poll 5s/10s.
4. **Dev** — [workflows/04-dev.md](../../workflows/04-dev.md) + skill `legacy-api`. Swagger `AppTags`.
5. **Test** — [workflows/05-test.md](../../workflows/05-test.md). `php artisan test --filter=...` pass.

Rồi skill `note-progress`. Trong log ghi đã qua 5 cổng.

Phase hiện tại vẫn theo `PROGRESS.md`. Phase 7 FCM không làm sớm.
