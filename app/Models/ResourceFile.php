<?php

namespace App\Models;

use App\Support\LegacyJson;
use App\Support\NotDeleted;
use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    protected $table = 'tb_resources';

    public $timestamps = false;

    protected $guarded = [];

    public static function alive()
    {
        return NotDeleted::apply(static::query());
    }

    public function toLegacyArray(): array
    {
        return [
            'path' => LegacyJson::str($this->path),
            'name' => LegacyJson::str($this->name),
            'creation_time' => LegacyJson::date($this->creation_time),
            'modification_time' => LegacyJson::date($this->modification_time),
            'file_size' => (int) $this->file_size,
            'file_type' => LegacyJson::str($this->file_type),
        ];
    }
}
