<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResourceFile extends Model
{
    protected $table = 'tb_resources';

    public $timestamps = false;

    protected $guarded = [];
}
