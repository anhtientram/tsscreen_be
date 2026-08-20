<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $table = 'tb_commands';

    protected $primaryKey = 'cmd_id';

    public $timestamps = false;

    protected $guarded = [];
}
