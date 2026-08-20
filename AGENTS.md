# TS Screen Backend — Agent instructions

Đọc [`.agents/README.md`](.agents/README.md) trước khi sửa code.

## Bắt buộc mỗi session

1. Xem phase hiện tại trong [`.agents/PROGRESS.md`](.agents/PROGRESS.md).
2. Làm đúng phase đó; không nhảy Phase 7 (Firebase lệnh) khi chưa xong 0–6.
3. Workflows: [`.agents/workflows.md`](.agents/workflows.md).
4. Rules: [`.agents/rules/`](.agents/rules/).
5. Skills: [`.agents/skills/`](.agents/skills/) — `implement-phase`, `note-progress`, `legacy-api`.

## Sau mỗi việc xong

Ghi log vào `.agents/PROGRESS.md` theo skill `note-progress`. Không được kết thúc task mà không note.
