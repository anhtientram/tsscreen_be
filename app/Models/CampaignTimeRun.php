<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;

class CampaignTimeRun extends Model
{
    protected $table = 'tb_campaign_time_runs';

    protected $primaryKey = 'id_run';

    public $timestamps = false;

    protected $guarded = [];

    public function toLegacyArray(): array
    {
        return [
            'id_run' => LegacyJson::str($this->id_run),
            'campaign_id' => LegacyJson::str($this->campaign_id),
            'from_time' => LegacyJson::str($this->from_time),
            'to_time' => LegacyJson::str($this->to_time),
        ];
    }
}
