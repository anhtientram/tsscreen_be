# Progress log

Cập nhật **ngay** khi xong một việc (endpoint, migration, phase, docs). Template: [`.agents/skills/note-progress/SKILL.md`](skills/note-progress/SKILL.md).

## Status

- **Current phase:** 0 (chưa code)
- **Last completed:** scaffold `.agents` (2026-08-20)
- **Blocked:** —

## Phase checklist

- [ ] 0 Nền (LegacyJson, routes, migrations `tb_*`, config, Swagger)
- [ ] 1 Auth customer + admin
- [ ] 2 Gói / đơn / hạn mức
- [ ] 3 Dir + device pairing
- [ ] 4 Campaign + lịch
- [ ] 5 Media quota + chunk
- [ ] 6 Notify in-app
- [ ] 7 Lệnh + Firebase từ server (không poll 5s/10s)

## Log

### 2026-08-20 — scaffold agent kit

- **Done:** Tạo `.agents` (workflows, rules, skills), `AGENTS.md`, `PROGRESS.md`. Chuyển workflows từ `docs/`.
- **Files:** `AGENTS.md`, `.agents/**`, `.cursor/rules/tsscreen.mdc`, `.cursor/skills/**`, `docs/workflows.md`
- **Next:** Phase 0 — LegacyJson, `routes/legacy.php`, migrations `tb_*`, `config6789.php`, Swagger
- **Notes:** Lệnh remote = Phase 7. Không implement poll 5s/10s. Password: customer plaintext, admin MD5, Google password rỗng.
