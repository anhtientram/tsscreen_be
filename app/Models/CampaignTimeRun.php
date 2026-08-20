<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignTimeRun extends Model
{
    protected $table = 'tb_campaign_time_runs';

    protected $primaryKey = 'id_run';

    public $timestamps = false;

    protected $guarded = [];
}
