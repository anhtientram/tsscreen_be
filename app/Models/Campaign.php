<?php

namespace App\Models;

use App\Support\LegacyJson;
use App\Support\NotDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    protected $table = 'tb_campaigns';

    protected $primaryKey = 'campaign_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function dir(): BelongsTo
    {
        return $this->belongsTo(DirectoryFolder::class, 'id_dir', 'id_dir');
    }

    public function timeRuns(): HasMany
    {
        return $this->hasMany(CampaignTimeRun::class, 'campaign_id', 'campaign_id');
    }

    public static function alive()
    {
        return NotDeleted::apply(static::query());
    }

    public function toLegacyArray(array $extra = []): array
    {
        $dir = $this->relationLoaded('dir') ? $this->dir : null;

        return array_merge([
            'campaign_id' => LegacyJson::str($this->campaign_id),
            'campaign_name' => LegacyJson::str($this->campaign_name),
            'status' => LegacyJson::str($this->status),
            'video_id' => LegacyJson::str($this->video_id),
            'from_date' => LegacyJson::str($this->from_date),
            'to_date' => LegacyJson::str($this->to_date),
            'from_time' => LegacyJson::str($this->from_time),
            'to_time' => LegacyJson::str($this->to_time),
            'days_of_week' => LegacyJson::str($this->days_of_week),
            'video_type' => LegacyJson::str($this->video_type),
            'url_youtobe' => LegacyJson::str($this->url_youtobe),
            'url_usp' => LegacyJson::str($this->url_usp),
            'customer_id' => LegacyJson::str($this->customer_id),
            'computer_id' => LegacyJson::str($this->computer_id),
            'id_dir' => LegacyJson::str($this->id_dir),
            'id_computer' => LegacyJson::str($this->id_computer),
            'video_duration' => LegacyJson::str($this->video_duration),
            'approved_yn' => LegacyJson::str($this->approved_yn === null || $this->approved_yn === '' ? '0' : $this->approved_yn),
            'default_yn' => LegacyJson::str($this->default_yn ?: '0'),
            'run_by_default_yn' => LegacyJson::str($this->run_by_default_yn ?: '0'),
            'default_campaign_id' => LegacyJson::str($this->default_campaign_id ?: '0'),
            'accept_count' => LegacyJson::str($this->accept_count),
            'accept_customers' => LegacyJson::str($this->accept_customers),
            'name_dir' => LegacyJson::str($dir?->name_dir),
            'deleted' => LegacyJson::str($this->deleted ?: 'n'),
        ], $extra);
    }
}
