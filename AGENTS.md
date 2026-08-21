# TS Screen Backend — Agent instructions

Đọc [`.agents/README.md`](.agents/README.md) trước khi sửa code.

## Bắt buộc mỗi session

1. Phase hiện tại: [`.agents/PROGRESS.md`](.agents/PROGRESS.md).
2. Làm đúng phase; không nhảy Phase 7 (Firebase lệnh) khi chưa xong 0–6.
3. **Mọi module/API:** 5 cổng trong [`.agents/workflows.md`](.agents/workflows.md) — phân tích 3 app → database → tối ưu → dev → test. Skill `build-module`.
4. Backlog phase: [`.agents/workflows/phases.md`](.agents/workflows/phases.md).
5. Rules: [`.agents/rules/`](.agents/rules/).
6. Skills: `build-module`, `implement-phase`, `note-progress`, `legacy-api`.

## Sau mỗi việc xong

Ghi [`.agents/PROGRESS.md`](.agents/PROGRESS.md). Không kết thúc task khi chưa test pass và chưa note.
