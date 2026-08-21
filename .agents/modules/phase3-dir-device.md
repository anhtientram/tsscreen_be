# Module: Dir + Device pairing

Phase: 3

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | GET/POST `/home/CreateDir`, `GetDirCustomer_ById/{id}`, `GetDir_ById`, `GetDirCustomer_SharedById`, `GetShareDir_ByCustomerId`, `UpDateDir_ById/{id}`, `DeleteDir_ById`, `InsertDirShare`, `GetSharedCustomerList_ByDirID`, `DeleteDir_shared/{id}/{cid}`, `UpDateOnOffDeviceDir_ById/{id}` | FormData name_dir, customer_id, type_dir; share: id_dir, customer_idfrom, customer_idto, checkOwner | `Dir_list`; share customers `userList`; create `msg` = id_dir | Có |
| Phone | Có | GET/POST CreateDevice (TV chủ yếu), GetDevice_ByComputerID, GetDevicesNotBelongAnyDir_ByCustomerId, GetDevice_ByIdDir (`Device_list`), UpDateDevice_ById, DeleteDevice_ById, GetListDeviceOfCamp_ByCampId (`Dir_list`!), InsertDeviceShare, GetSharedCustomerList_ByComputeID, GetDeviceCustomer_SharedById, GetSharedDevices_ByCustomerId, DeleteDevice_shared | FormData: computer_name, seri_computer, status, provinces, district, wards, center_id, location, customer_id, type, id_dir, time_end | Device_list; share Device_list | Có |
| TV | Có | POST CreateDevice, GET GetDevices_ByCustomerId, POST UpDateDevice_ById, POST UpdateRomMemory/{id}, GET GetDirCustomer_ById, GetDirCustomer_SharedById, GET UpdateComputerToken_ById/{id}/{token} | Create: seri_computer (unknown→androidId), status 1, center_id 5, type chủ sở hữu, id_dir | Device_list / Dir_list | Có |
| Admin | Có | GET GetDirCustomer_ById, GetDevice_ByIdDir, GetDevicesNotBelongAnyDir_ByCustomerId, DeleteDevice_ById | path id | Dir_list / Device_list | Có |

Typo / exception: `seri_computer`; `GetSharedCustomerList_ByComputeID`; Phone Device `int.parse` turn_on/turn_off/isCheckOnProjector/isCheckOffProjector; TV Dir `int.parse(created_by)` — always numeric string. CreateDir `msg` = id_dir.

## Cổng 2 — Database

Bảng: `tb_dirs`, `tb_dir_shares`, `tb_devices`, `tb_device_shares`  
Cột thêm/sửa: không  
Index: đã có customer_id, seri_computer, id_dir  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- List device `with(dir, customer)` — không N+1.
- CreateDevice: `PacketQuota::canAddDevice` server-side (limit_qty). Serial trùng cùng customer → cập nhật, không đếm thêm.
- Heartbeat UpdateAliveTimeDevice_ById 60s giữ (1 req/phút). Không poll lệnh.

## Cổng 4 — Dev

Files: DirController, DeviceController, models toLegacyArray, routes/legacy.php

## Cổng 5 — Test

`tests/Feature/LegacyDirDeviceTest.php` — pass (`php artisan test --filter=LegacyDirDevice`)
