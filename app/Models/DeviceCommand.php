<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $table = 'tb_commands';

    protected $primaryKey = 'cmd_id';

    public $timestamps = false;

    protected $guarded = [];

    public function toLegacyArray(): array
    {
        $commit = LegacyJson::date($this->commit_time);
        $return = LegacyJson::date($this->return_time);
        if ($return === '') {
            $return = $commit;
        }

        return [
            'cmd_id' => LegacyJson::str($this->cmd_id),
            'cmd_code' => LegacyJson::str($this->cmd_code),
            'commit_time' => $commit,
            'content' => LegacyJson::str($this->content),
            'is_imme' => LegacyJson::str($this->is_imme === null || $this->is_imme === '' ? '0' : $this->is_imme),
            'return_time' => $return,
            'return_value' => LegacyJson::str($this->return_value),
            'sn' => LegacyJson::str($this->sn),
            'sync' => LegacyJson::str($this->sync === null || $this->sync === '' ? '0' : $this->sync),
            'done' => LegacyJson::str($this->done === null || $this->done === '' ? '0' : $this->done),
            'second_wait' => LegacyJson::str($this->second_wait === null || $this->second_wait === '' ? '10' : $this->second_wait),
        ];
    }
}
