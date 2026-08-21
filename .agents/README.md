# `.agents` — nguồn sự thật cho AI và team

```
.agents/
  README.md
  PROGRESS.md
  workflows.md           ← 5 cổng bắt buộc mỗi API
  workflows/phases.md    ← backlog phase 0–7
  workflows/01–05-*.md   ← chi tiết từng cổng
  templates/module-card.md
  rules/
  skills/                ← build-module, implement-phase, …
```

Root [`AGENTS.md`](../AGENTS.md) trỏ vào đây.

| Cần | Đọc |
|-----|-----|
| Đang làm phase nào | `PROGRESS.md` |
| Cách làm 1 API | `workflows.md` + skill `build-module` |
| Sequence phase 0–7 | `workflows/phases.md` |
| Path JSON, bảng, media, lệnh | `rules/` |
| Ghi note | skill `note-progress` |

3 app Flutter (không sửa phase đầu): `RemoteProjector2024` (phone), `remote_projector_tv` (TV), `RemoteProjectorAdmin` (admin).
