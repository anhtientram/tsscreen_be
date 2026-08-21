<?php

namespace App\Models;

use App\Support\LegacyJson;
use App\Support\NotDeleted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DirectoryFolder extends Model
{
    protected $table = 'tb_dirs';

    protected $primaryKey = 'id_dir';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'id_dir', 'id_dir');
    }

    public function toLegacyArray(array $extra = []): array
    {
        $createdBy = $this->created_by ?: $this->customer_id;

        return array_merge([
            'id_dir' => LegacyJson::str($this->id_dir),
            'name_dir' => LegacyJson::str($this->name_dir),
            'customer_id' => LegacyJson::str($this->customer_id),
            'type_dir' => LegacyJson::str($this->type_dir),
            'created_by' => LegacyJson::str($createdBy),
            'created_date' => LegacyJson::date($this->created_date),
            'last_MDF_by' => LegacyJson::str($this->last_MDF_by),
            'last_MDF_date' => LegacyJson::date($this->last_MDF_date),
            'deleted' => LegacyJson::str($this->deleted ?: 'n'),
            'turnon_time' => LegacyJson::str($this->turnon_time),
            'turnoff_time' => LegacyJson::str($this->turnoff_time),
        ], $extra);
    }

    public static function alive()
    {
        return NotDeleted::apply(static::query());
    }
}
