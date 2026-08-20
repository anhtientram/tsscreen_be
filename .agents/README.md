# `.agents` — nguồn sự thật cho AI và team

```
.agents/
  README.md          ← file này
  PROGRESS.md        ← log bắt buộc sau mỗi việc xong
  workflows.md       ← phase 0–7, sequence, endpoint
  rules/             ← quy ước luôn tuân thủ
  skills/            ← hướng dẫn làm từng loại việc
```

Root [`AGENTS.md`](../AGENTS.md) trỏ vào đây (Cursor luôn load).

| Cần | Đọc |
|-----|-----|
| Đang làm phase nào | `PROGRESS.md` |
| Luồng nghiệp vụ / API | `workflows.md` |
| Path JSON, bảng `tb_*`, media, lệnh | `rules/` |
| Implement một phase | `skills/implement-phase/SKILL.md` |
| Ghi note khi xong | `skills/note-progress/SKILL.md` |
| Viết endpoint kiểu PHP cũ | `skills/legacy-api/SKILL.md` |

3 app Flutter (không sửa ở phase đầu): `RemoteProjector2024` (phone), `remote_projector_tv` (TV), `RemoteProjectorAdmin` (admin).
