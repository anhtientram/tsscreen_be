<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    protected $table = 'tb_notifications';

    protected $primaryKey = 'id_notify';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = null;

    protected $guarded = [];

    public function toLegacyArray(): array
    {
        return [
            'id_notify' => LegacyJson::str($this->id_notify),
            'customer_id' => LegacyJson::str($this->customer_id),
            'title' => LegacyJson::str($this->title),
            'descript' => LegacyJson::str($this->descript),
            'detail' => LegacyJson::str($this->detail),
            'picture' => LegacyJson::str($this->picture),
            'seen' => LegacyJson::str($this->seen === null || $this->seen === '' ? '0' : $this->seen),
            'created_date' => LegacyJson::date($this->created_date),
        ];
    }
}
