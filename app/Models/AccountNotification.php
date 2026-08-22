<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;

class AccountNotification extends Model
{
    protected $table = 'tb_account_notifications';

    protected $primaryKey = 'id_notify';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = null;

    protected $guarded = [];

    public function toLegacyArray(): array
    {
        return [
            'id_notify' => LegacyJson::str($this->id_notify),
            'account_id' => LegacyJson::str($this->account_id),
            'title' => LegacyJson::str($this->title),
            'descript' => LegacyJson::str($this->descript),
            'detail' => LegacyJson::str($this->detail),
            'picture' => LegacyJson::str($this->picture),
            'seen' => LegacyJson::str($this->seen === null || $this->seen === '' ? '0' : $this->seen),
            'created_date' => LegacyJson::date($this->created_date),
        ];
    }
}
