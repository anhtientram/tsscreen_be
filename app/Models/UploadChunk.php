<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadChunk extends Model
{
    protected $table = 'tb_upload_chunks';

    public $timestamps = false;

    protected $guarded = [];
}
