# Module: Campaign + lịch TV

Phase: 4

## Cổng 1 — Phân tích 3 app

| App | Gọi? | Method + path | Request | Response key | jsonDecode? |
|-----|------|---------------|---------|--------------|-------------|
| Phone | Có | CreateCamp POST (toJson + url_yotobe), UpdateCamp_ById, DeleteCamp_ById GET, ApproveCamp_ById POST approved_yn, GetAllCamp_ById → **camp_list**, Getcamp_ByComputerId/{id}/{status} Camp_list, Getcamp_ByDirId/{id}/{status} Camp_list, AddTimeRun_ByCamp, UpdateTimeRun_ByIdRun, DeleteTimeRun_ByIdRun, GetTimeRun_ByCampId → camp_list_time, GetTimeRun_ByCampId_1/{camp}/{dir}, GetCampaignRunProfile Profile_list, GetCampaignRunProfile_Genaral Profile_list (typo), UpdateDefaultCamp_ById, UpdateRunByDefaultCamp_ById/{id}/{0\|1}, GetCamp_SharedByCustomerId, GetShareCamp_ByCustomerId, GetDefaultTimeRun_ByIdDir | FormData camp fields; url_youtobe + url_yotobe; url_usp | camp_list / Camp_list / camp_list_time / Profile_list | Có |
| TV | Có | GET GetCampToday_ByComputerId/{id}/{date}/1 Camp_list; POST GetAllRunTimeOfComputer_4 computer_id work_date Camp_list; GET GetTimeRun_ByCampId camp_list_time; POST AddCampaignRunProfile; GET Getcamp_ByDirId/{id}/1; Getcamp_ByComputerId | work_date UTC+7 Y-m-d; run profile computer_id = customer_id (bug app) + seri_computer | Camp_list / camp_list_time | Có |
| Admin | Không | — | — | — | — |

Typo: `url_youtobe`, `url_usp`, `url_yotobe` (create extra), `GetCampaignRunProfile_Genaral`, `default_campaign_id` TV `int.parse` → gửi `'0'`. Trailing `/1` = chỉ approved_yn=1. **Phone thêm video 1 máy: FormData `id_computer` = `computer_id` (field `computer_id` thường rỗng). `id_computer`/`computer_id` rỗng + `id_dir` = cả hệ thống.**

## Cổng 2 — Database

Bảng: `tb_campaigns`, `tb_campaign_time_runs`, `tb_campaign_run_profiles`  
Không đổi DB: ☑

## Cổng 3 — Tối ưu

- Lịch TV: 1 query camp theo máy (computer_id / id_computer) + camp cả dir (cả hai cột máy rỗng) + 1 query time_runs `whereIn campaign_id` (không N+1). Camp gắn 1 TV không lộ sang TV khác cùng dir.
- Không gửi VIDEO_FROMCAMP (Phase 7). TV tự load lịch.
- Map run profile: computer_id hoặc seri_computer.

## Cổng 4 — Dev

CampaignController + routes

## Cổng 5 — Test

`tests/Feature/LegacyCampaignTest.php` — pass
