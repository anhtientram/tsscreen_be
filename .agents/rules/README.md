# Rules

Luôn tuân thủ. Chi tiết luồng domain: [`../workflows/phases.md`](../workflows/phases.md). Pipeline 5 cổng: [`../workflows.md`](../workflows.md).

| File | Việc |
|------|------|
| [00-core](00-core.md) | Phase, 5 cổng, Flutter, JSON string, typo, note progress |
| [01-legacy-api](01-legacy-api.md) | Helper response, list keys, password |
| [02-schema](02-schema.md) | Bảng `tb_*`, hạn mức gói |
| [03-media](03-media.md) | Upload, quota, disk 85% |
| [04-commands](04-commands.md) | Không poll; FCM Phase 7 |
| [05-progress](05-progress.md) | Bắt buộc ghi `PROGRESS.md` |
| [06-pipeline](06-pipeline.md) | Phân tích → DB → tối ưu → dev → test |
