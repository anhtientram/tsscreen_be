<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\CampaignRunProfile;
use App\Models\CampaignTimeRun;
use App\Models\Customer;
use App\Models\Device;
use App\OpenApi\AppTags;
use App\Support\LegacyCustomerResolver;
use App\Support\LegacyJson;
use Carbon\Carbon;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CampaignController extends Controller
{
    #[OA\Post(path: '/home/CreateCamp', summary: 'Phone tạo camp. id_computer = video riêng 1 TV; chỉ id_dir = cả hệ thống', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function create(Request $request)
    {
        $customer = LegacyCustomerResolver::resolve($request);
        if (! $customer || ! $customer->isActive()) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy khách hàng']);
        }

        $request->merge(['customer_id' => $customer->customer_id]);

        try {
            $camp = Campaign::query()->create($this->attrsFromRequest($request, [
                'deleted' => 'n',
            ]));
        } catch (\Throwable $e) {
            report($e);

            return LegacyJson::send(['status' => -2, 'msg' => 'Không tạo được chiến dịch']);
        }

        return LegacyJson::send(['status' => 1, 'msg' => LegacyJson::str($camp->campaign_id)]);
    }

    #[OA\Post(path: '/home/UpdateCamp_ById/{campaignId}', summary: 'Sửa campaign', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function update(Request $request, string $campaignId)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if (! $camp) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy chiến dịch']);
        }

        $camp->fill($this->attrsFromRequest($request, existing: $camp));
        $camp->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/DeleteCamp_ById/{campaignId}', summary: 'Xóa mềm campaign', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function destroy(string $campaignId)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if (! $camp) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy chiến dịch']);
        }

        $camp->deleted = 'y';
        $camp->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/ApproveCamp_ById/{campaignId}', summary: 'Duyệt camp approved_yn 0|1|-1', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function approve(Request $request, string $campaignId)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if (! $camp) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy chiến dịch']);
        }

        $camp->approved_yn = LegacyJson::str($request->input('approved_yn', '1'));
        $camp->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetAllCamp_ById/{customerId}', summary: 'Phone list — key camp_list (lowercase)', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function allByCustomer(string $customerId)
    {
        $list = Campaign::alive()
            ->with('dir')
            ->where('customer_id', $customerId)
            ->orderByDesc('campaign_id')
            ->get()
            ->map(fn (Campaign $c) => $c->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['camp_list' => $list]);
    }

    #[OA\Get(path: '/home/Getcamp_ByComputerId/{computerId}/{status}', summary: 'Camp theo TV; status 1|all', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byComputer(string $computerId, string $status = 'all')
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        $query = Campaign::alive()->with('dir');
        $this->restrictToComputer($query, $computerId, $device);
        $this->applyApprovedFilter($query, $status);

        $list = $query->orderBy('campaign_id')->get()->map(fn (Campaign $c) => $c->toLegacyArray())->values()->all();

        return LegacyJson::send(['Camp_list' => $list]);
    }

    #[OA\Get(path: '/home/Getcamp_ByDirId/{idDir}/{status}', summary: 'Camp theo dir; /1 = approved', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function byDir(string $idDir, string $status = 'all')
    {
        $query = Campaign::alive()->with('dir')->where('id_dir', $idDir);
        $this->applyApprovedFilter($query, $status);
        $list = $query->orderBy('campaign_id')->get()->map(fn (Campaign $c) => $c->toLegacyArray())->values()->all();

        return LegacyJson::send(['Camp_list' => $list]);
    }

    #[OA\Get(path: '/home/GetCampToday_ByComputerId/{computerId}/{date}/{flag}', summary: 'TV lịch hôm nay UTC+7', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function today(string $computerId, string $date, string $flag = '1')
    {
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device) {
            return LegacyJson::send(['Camp_list' => []]);
        }

        $query = Campaign::alive()->with('dir');
        $this->restrictToComputer($query, $computerId, $device);
        $this->applyApprovedFilter($query, $flag);

        $list = $query->get()
            ->filter(fn (Campaign $c) => $this->matchesDate($c, $date) && $this->matchesDay($c->days_of_week, $date))
            ->map(function (Campaign $c) {
                $row = $c->toLegacyArray();
                if ((string) $c->run_by_default_yn === '1' && $c->id_dir) {
                    $default = Campaign::alive()
                        ->where('id_dir', $c->id_dir)
                        ->where('default_yn', '1')
                        ->value('campaign_id');
                    $row['default_campaign_id'] = LegacyJson::str($default ?: '0');
                }

                return $row;
            })
            ->values()
            ->all();

        return LegacyJson::send(['Camp_list' => $list]);
    }

    #[OA\Post(path: '/home/GetAllRunTimeOfComputer_4', summary: 'TV schedule theo work_date', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function runTimesOfComputer(Request $request)
    {
        $computerId = $request->input('computer_id');
        $workDate = substr((string) $request->input('work_date'), 0, 10);
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device) {
            return LegacyJson::send(['Camp_list' => []]);
        }

        $query = Campaign::alive();
        $this->restrictToComputer($query, (string) $computerId, $device);
        $this->applyApprovedFilter($query, '1');

        $camps = $query->get()->filter(
            fn (Campaign $c) => $this->matchesDate($c, $workDate) && $this->matchesDay($c->days_of_week, $workDate)
        );
        $timeRuns = CampaignTimeRun::query()
            ->whereIn('campaign_id', $camps->pluck('campaign_id'))
            ->get()
            ->groupBy('campaign_id');

        $list = [];
        foreach ($camps as $camp) {
            $slots = $timeRuns->get($camp->campaign_id) ?? collect();
            if ($slots->isEmpty()) {
                $list[] = $this->scheduleRow($camp, $camp->from_time, $camp->to_time);

                continue;
            }
            foreach ($slots as $slot) {
                $list[] = $this->scheduleRow($camp, $slot->from_time, $slot->to_time);
            }
        }

        return LegacyJson::send(['Camp_list' => $list]);
    }

    #[OA\Get(path: '/home/GetTimeRun_ByCampId/{campaignId}', summary: 'Khung giờ camp_list_time', tags: [AppTags::CUSTOMER, AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function timeRuns(string $campaignId)
    {
        $list = CampaignTimeRun::query()
            ->where('campaign_id', $campaignId)
            ->orderBy('id_run')
            ->get()
            ->map(fn (CampaignTimeRun $t) => $t->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['camp_list_time' => $list]);
    }

    #[OA\Get(path: '/home/GetTimeRun_ByCampId_1/{campaignId}/{idDir}', summary: 'Time run + fallback default dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function timeRunsWithDir(string $campaignId, string $idDir)
    {
        $list = CampaignTimeRun::query()
            ->where('campaign_id', $campaignId)
            ->orderBy('id_run')
            ->get();

        if ($list->isEmpty()) {
            $defaultId = Campaign::alive()
                ->where('id_dir', $idDir)
                ->where('default_yn', '1')
                ->value('campaign_id');
            if ($defaultId) {
                $list = CampaignTimeRun::query()->where('campaign_id', $defaultId)->orderBy('id_run')->get();
            }
        }

        return LegacyJson::send([
            'camp_list_time' => $list->map(fn (CampaignTimeRun $t) => $t->toLegacyArray())->values()->all(),
        ]);
    }

    #[OA\Post(path: '/home/AddTimeRun_ByCamp', summary: 'Thêm khung giờ', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function addTimeRun(Request $request)
    {
        CampaignTimeRun::query()->create([
            'campaign_id' => $request->input('campaign_id'),
            'from_time' => $request->input('from_time'),
            'to_time' => $request->input('to_time'),
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/UpdateTimeRun_ByIdRun', summary: 'Sửa khung giờ', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function updateTimeRun(Request $request)
    {
        $run = CampaignTimeRun::query()->where('id_run', $request->input('id_run'))->first();
        if ($run) {
            $run->from_time = $request->input('from_time', $run->from_time);
            $run->to_time = $request->input('to_time', $run->to_time);
            $run->save();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/DeleteTimeRun_ByIdRun/{idRun}', summary: 'Xóa khung giờ', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function deleteTimeRun(string $idRun)
    {
        CampaignTimeRun::query()->where('id_run', $idRun)->delete();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetDefaultTimeRun_ByIdDir/{idDir}', summary: 'Time run camp mặc định của dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function defaultTimeRun(string $idDir)
    {
        $defaultId = Campaign::alive()
            ->where('id_dir', $idDir)
            ->where('default_yn', '1')
            ->value('campaign_id');

        $list = $defaultId
            ? CampaignTimeRun::query()->where('campaign_id', $defaultId)->orderBy('id_run')->get()
            : collect();

        return LegacyJson::send([
            'camp_list_time' => $list->map(fn (CampaignTimeRun $t) => $t->toLegacyArray())->values()->all(),
        ]);
    }

    #[OA\Get(path: '/home/UpdateDefaultCamp_ById/{campaignId}', summary: 'Đặt camp mặc định dir', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function setDefault(string $campaignId)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if (! $camp) {
            return LegacyJson::send(['status' => -2, 'msg' => 'Không tìm thấy chiến dịch']);
        }

        $camp->default_yn = '1';
        $camp->save();

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/UpdateRunByDefaultCamp_ById/{campaignId}/{used}', summary: 'Dùng lịch camp mặc định', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function setRunByDefault(string $campaignId, string $used)
    {
        $camp = Campaign::alive()->where('campaign_id', $campaignId)->first();
        if ($camp) {
            $camp->run_by_default_yn = $used === '1' ? '1' : '0';
            $camp->save();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/home/GetCamp_SharedByCustomerId/{customerId}', summary: 'Camp share tới khách', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function sharedToCustomer(string $customerId)
    {
        return $this->sharedCamps($customerId);
    }

    #[OA\Get(path: '/home/GetShareCamp_ByCustomerId/{customerId}', summary: 'Camp khách chia sẻ / nhận', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function shareCampByCustomer(string $customerId)
    {
        return $this->sharedCamps($customerId);
    }

    #[OA\Post(path: '/home/AddCampaignRunProfile', summary: 'TV ghi log chạy; map seri_computer nếu computer_id = customer_id', tags: [AppTags::PROJECTOR], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function addRunProfile(Request $request)
    {
        $computerId = $request->input('computer_id');
        $serial = $request->input('seri_computer');
        $device = Device::alive()->where('computer_id', $computerId)->first();
        if (! $device && $serial) {
            $device = Device::alive()->where('seri_computer', $serial)->first();
        }

        CampaignRunProfile::query()->create([
            'customer_id' => $request->input('customer_id'),
            'customer_name' => $request->input('customer_name'),
            'campaign_id' => $request->input('campaign_id'),
            'campaign_name' => $request->input('campaign_name'),
            'url' => $request->input('url'),
            'computer_id' => $device?->computer_id ?: $computerId,
            'seri_computer' => $serial ?: $device?->seri_computer,
            'computer_name' => $request->input('computer_name', $device?->computer_name),
            'run_time' => LegacyJson::str($request->input('run_time')),
            'run_time_server' => now(),
        ]);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/GetCampaignRunProfile', summary: 'Chi tiết lần chạy', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function runProfile(Request $request)
    {
        $query = CampaignRunProfile::query()->where('campaign_id', $request->input('campaign_id'));
        $this->applyProfileDate($query, $request);

        $list = $query->orderByDesc('id')->get()->map(fn (CampaignRunProfile $p) => [
            'id' => LegacyJson::str($p->id),
            'campaign_id' => LegacyJson::str($p->campaign_id),
            'campaign_name' => LegacyJson::str($p->campaign_name),
            'url' => LegacyJson::str($p->url),
            'computer_id' => LegacyJson::str($p->computer_id),
            'seri_computer' => LegacyJson::str($p->seri_computer),
            'computer_name' => LegacyJson::str($p->computer_name),
            'run_time' => LegacyJson::str($p->run_time),
            'run_time_server' => LegacyJson::date($p->run_time_server),
        ])->values()->all();

        return LegacyJson::send(['Profile_list' => $list]);
    }

    #[OA\Post(path: '/home/GetCampaignRunProfile_Genaral', summary: 'Thống kê theo ngày — typo Genaral', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function runProfileGeneral(Request $request)
    {
        $query = CampaignRunProfile::query()->where('customer_id', $request->input('customer_id'));
        $this->applyProfileDate($query, $request);

        $rows = $query->get();
        $grouped = [];
        foreach ($rows as $p) {
            $day = substr((string) ($p->run_time ?: $p->run_time_server), 0, 10);
            $key = $p->campaign_id.'|'.$day;
            if (! isset($grouped[$key])) {
                $grouped[$key] = [
                    'campaign_id' => LegacyJson::str($p->campaign_id),
                    'campaign_name' => LegacyJson::str($p->campaign_name),
                    'run_date' => $day,
                    'run_total' => 0,
                ];
            }
            $grouped[$key]['run_total']++;
        }

        $list = array_map(function (array $row) {
            $row['run_total'] = LegacyJson::str($row['run_total']);

            return $row;
        }, array_values($grouped));

        return LegacyJson::send(['Profile_list' => $list]);
    }

    private function sharedCamps(string $customerId)
    {
        $list = Campaign::alive()
            ->with('dir')
            ->where('customer_id', '!=', $customerId)
            ->where(function ($q) use ($customerId): void {
                $q->where('accept_customers', 'like', '%'.$customerId.'%');
            })
            ->get()
            ->map(fn (Campaign $c) => $c->toLegacyArray(['is_owner' => '0']))
            ->values()
            ->all();

        return LegacyJson::send(['Camp_list' => $list]);
    }

    private function attrsFromRequest(Request $request, array $extra = [], ?Campaign $existing = null): array
    {
        $url = $request->input('url_youtobe') ?: $request->input('url_yotobe');
        $idDir = $request->input('id_dir');
        $targetComputer = $this->targetComputerId($request);

        return array_merge([
            'campaign_name' => $request->input('campaign_name'),
            'status' => $request->input('status') ?: '1',
            'video_id' => $request->input('video_id') ?: '',
            'from_date' => $request->input('from_date') ?: '',
            'to_date' => $request->input('to_date') ?: '',
            'from_time' => $request->input('from_time') ?: '',
            'to_time' => $request->input('to_time') ?: '',
            'days_of_week' => $request->input('days_of_week') ?: '',
            'video_type' => $request->input('video_type') ?: 'url',
            'url_youtobe' => $url ?: '',
            'url_usp' => $request->input('url_usp') ?: '',
            'customer_id' => $request->input('customer_id'),
            'computer_id' => $targetComputer,
            'id_dir' => ($idDir === '' || $idDir === null) ? null : $idDir,
            'id_computer' => $targetComputer !== null ? LegacyJson::str($targetComputer) : '',
            'video_duration' => $request->input('video_duration') ?: '',
            'approved_yn' => $this->resolveFlag($request, 'approved_yn', $existing?->approved_yn ?? '0'),
            'default_yn' => $this->resolveFlag($request, 'default_yn', $existing?->default_yn ?? '0'),
            'run_by_default_yn' => $this->resolveFlag($request, 'run_by_default_yn', $existing?->run_by_default_yn ?? '0'),
        ], $extra);
    }

    private function targetComputerId(Request $request): mixed
    {
        $computerId = $request->input('computer_id');
        $idComputer = $request->input('id_computer');
        if ($this->filledComputerId($computerId)) {
            return $computerId;
        }
        if ($this->filledComputerId($idComputer)) {
            return $idComputer;
        }

        return null;
    }

    private function filledComputerId(mixed $value): bool
    {
        $s = trim((string) $value);

        return $s !== '' && $s !== '0';
    }

    private function restrictToComputer($query, string $computerId, ?Device $device): void
    {
        $query->where(function ($q) use ($computerId, $device): void {
            $q->where(function ($own) use ($computerId): void {
                $own->where('computer_id', $computerId)
                    ->orWhere('id_computer', $computerId);
            });
            if ($device?->id_dir) {
                $q->orWhere(function ($dir) use ($device): void {
                    $dir->where('id_dir', $device->id_dir)
                        ->where(function ($empty) {
                            $empty->whereNull('computer_id')->orWhere('computer_id', '')->orWhere('computer_id', '0');
                        })
                        ->where(function ($empty) {
                            $empty->whereNull('id_computer')->orWhere('id_computer', '')->orWhere('id_computer', '0');
                        });
                });
            }
        });
    }

    private function applyApprovedFilter($query, string $status): void
    {
        $status = strtolower($status);
        if ($status === 'all' || $status === '') {
            return;
        }
        if ($status === '1' || $status === 'active') {
            $query->where('approved_yn', '1');
        } elseif ($status === '0' || $status === '-1') {
            $query->where('approved_yn', $status);
        }
    }

    private function matchesDate(Campaign $camp, string $date): bool
    {
        $day = substr($date, 0, 10);
        $from = substr((string) $camp->from_date, 0, 10);
        $to = substr((string) $camp->to_date, 0, 10);
        if ($from !== '' && $day < $from) {
            return false;
        }
        if ($to !== '' && $day > $to) {
            return false;
        }

        return true;
    }

    private function matchesDay(?string $daysOfWeek, string $date): bool
    {
        $days = trim((string) $daysOfWeek);
        if ($days === '') {
            return true;
        }

        $iso = Carbon::parse(substr($date, 0, 10))->isoWeekday();
        $labels = [
            1 => ['T2', '2'],
            2 => ['T3', '3'],
            3 => ['T4', '4'],
            4 => ['T5', '5'],
            5 => ['T6', '6'],
            6 => ['T7', '7'],
            7 => ['CN', '8', 'T8', '0'],
        ];

        foreach ($labels[$iso] ?? [] as $label) {
            if (preg_match('/\b'.preg_quote($label, '/').'\b/u', $days) || str_contains($days, $label)) {
                return true;
            }
        }

        return false;
    }

    private function resolveFlag(Request $request, string $key, string $fallback = '0'): string
    {
        if (! $request->has($key)) {
            return $this->normalizeFlag($fallback);
        }

        $value = $request->input($key);
        if ($value === null || $value === '') {
            return $this->normalizeFlag($fallback);
        }

        return $this->normalizeFlag($value);
    }

    private function normalizeFlag(mixed $value): string
    {
        if ($value === true || $value === 1 || $value === '1' || $value === 'true' || $value === 'y' || $value === 'yes') {
            return '1';
        }

        if ($value === false || $value === 0 || $value === '0' || $value === 'false' || $value === 'n' || $value === 'no') {
            return '0';
        }

        return LegacyJson::str($value);
    }

    private function flag(Request $request, string $key, string $default = '0'): string
    {
        return $this->resolveFlag($request, $key, $default);
    }

    private function scheduleRow(Campaign $camp, ?string $fromTime, ?string $toTime): array
    {
        return [
            'campaign_id' => LegacyJson::str($camp->campaign_id),
            'campaign_name' => LegacyJson::str($camp->campaign_name),
            'from_date' => LegacyJson::str($camp->from_date),
            'to_date' => LegacyJson::str($camp->to_date),
            'status' => LegacyJson::str($camp->status),
            'video_type' => LegacyJson::str($camp->video_type),
            'url_youtobe' => LegacyJson::str($camp->url_youtobe),
            'url_usp' => LegacyJson::str($camp->url_usp),
            'days_of_week' => LegacyJson::str($camp->days_of_week),
            'deleted' => LegacyJson::str($camp->deleted ?: 'n'),
            'customer_id' => LegacyJson::str($camp->customer_id),
            'from_time' => LegacyJson::str($fromTime),
            'to_time' => LegacyJson::str($toTime),
            'video_duration' => LegacyJson::str($camp->video_duration),
        ];
    }

    private function applyProfileDate($query, Request $request): void
    {
        $from = $request->input('from_date');
        $to = $request->input('to_date');
        if ($from) {
            $query->where(function ($q) use ($from): void {
                $q->where('run_time', '>=', $from)
                    ->orWhereDate('run_time_server', '>=', $from);
            });
        }
        if ($to) {
            $query->where(function ($q) use ($to): void {
                $q->where('run_time', '<=', $to.' 23:59:59')
                    ->orWhereDate('run_time_server', '<=', $to);
            });
        }
    }
}
