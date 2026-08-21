---
name: note-progress
description: Append a completion note to .agents/PROGRESS.md after every finished task. Use when a phase, endpoint, migration, fix, or docs change is done.
---

# Note progress

Sau mỗi việc **xong**, sửa `.agents/PROGRESS.md`:

1. Đầu file **Status**: `Current phase`, `Last completed`, `Blocked`.
2. Tick checklist nếu xong cả phase; tăng Current phase.
3. **Prepend** mục log mới dưới `## Log` (mới nhất trên cùng):

```markdown
### YYYY-MM-DD — tiêu đề ngắn

- **Done:** ...
- **Pipeline:** phân tích 3 app / DB / tối ưu / dev / test
- **Files:** path1, path2
- **Next:** việc tiếp theo
- **Notes:** (tuỳ chọn)
```

Ngày dùng ngày thật của session. Không xoá log cũ. Không gộp nhiều ngày thành một dòng mơ hồ.
