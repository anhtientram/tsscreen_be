# Pipeline 5 cổng

Mỗi endpoint/module mới hoặc sửa behavior:

1. Phân tích 3 app (`RemoteProjector2024`, `remote_projector_tv`, `RemoteProjectorAdmin`)
2. Database chỉ cột app cần (`tb_*`)
3. Tối ưu (query, quota, không poll)
4. Dev (legacy path + Swagger `AppTags`)
5. Test Feature pass

Chi tiết: [../workflows.md](../workflows.md). Skill: `build-module`.
