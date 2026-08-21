# Ghi progress

Khi xong bất kỳ mục nào (migration, endpoint, phase, docs, bugfix):

1. Mở `.agents/PROGRESS.md`
2. Endpoint/module: xác nhận đã qua 5 cổng (phân tích 3 app → DB → tối ưu → dev → test)
3. Thêm mục log (ngày, Done, Pipeline, Files, Next, Notes)
4. Nếu xong cả phase: tick checklist, đổi **Current phase** sang số tiếp theo
5. Blocked thì ghi **Blocked**

Không được “xong rồi tính note sau”. Không note = việc chưa xong.
