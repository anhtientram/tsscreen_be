<?php

namespace App\Models;

use App\Support\LegacyJson;
use App\Support\NotDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $table = 'tb_devices';

    protected $primaryKey = 'computer_id';

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

    public static function alive()
    {
        return NotDeleted::apply(static::query());
    }

    public function toLegacyArray(array $extra = []): array
    {
        $dir = $this->relationLoaded('dir') ? $this->dir : null;
        $customer = $this->relationLoaded('customer') ? $this->customer : null;

        return array_merge([
            'computer_id' => LegacyJson::str($this->computer_id),
            'computer_name' => LegacyJson::str($this->computer_name),
            'seri_computer' => LegacyJson::str($this->seri_computer),
            'ip_address' => LegacyJson::str($this->ip_address),
            'status' => LegacyJson::str($this->status === '' || $this->status === null ? '0' : $this->status),
            'provinces' => LegacyJson::str($this->provinces),
            'district' => LegacyJson::str($this->district),
            'wards' => LegacyJson::str($this->wards),
            'center_id' => LegacyJson::str($this->center_id),
            'location' => LegacyJson::str($this->location),
            'actived_date' => LegacyJson::str($this->actived_date),
            'created_date' => LegacyJson::date($this->created_date),
            'ultraviewPW' => LegacyJson::str($this->ultraviewPW),
            'ultraviewID' => LegacyJson::str($this->ultraviewID),
            'customer_id' => LegacyJson::str($this->customer_id),
            'customer_name' => LegacyJson::str($this->customer_name ?: $customer?->customer_name),
            'type' => LegacyJson::str($this->type),
            'id_dir' => $this->id_dir ? LegacyJson::str($this->id_dir) : '',
            'name_dir' => LegacyJson::str($dir?->name_dir),
            'time_end' => LegacyJson::str($this->time_end),
            'turn_on' => LegacyJson::str($this->turn_on !== null && $this->turn_on !== '' ? $this->turn_on : '0'),
            'turn_off' => LegacyJson::str($this->turn_off !== null && $this->turn_off !== '' ? $this->turn_off : '0'),
            'created_by' => LegacyJson::str($this->created_by ?: $this->customer_id),
            'last_MDF_by' => LegacyJson::str($this->last_MDF_by),
            'last_MDF_date' => LegacyJson::date($this->last_MDF_date),
            'user' => LegacyJson::str($this->user),
            'pass' => LegacyJson::str($this->pass),
            'deleted' => LegacyJson::str($this->deleted ?: 'n'),
            'isCheckOnProjector' => LegacyJson::str($this->isCheckOnProjector !== null && $this->isCheckOnProjector !== '' ? $this->isCheckOnProjector : '0'),
            'isCheckOffProjector' => LegacyJson::str($this->isCheckOffProjector !== null && $this->isCheckOffProjector !== '' ? $this->isCheckOffProjector : '0'),
            'lasted_alive_time' => LegacyJson::date($this->lasted_alive_time),
            'computer_token' => LegacyJson::str($this->computer_token),
            'rom_memory_total' => LegacyJson::str($this->rom_memory_total),
            'rom_memory_used' => LegacyJson::str($this->rom_memory_used),
            'turnon_time' => LegacyJson::str($dir?->turnon_time),
            'turnoff_time' => LegacyJson::str($dir?->turnoff_time),
        ], $extra);
    }
}
