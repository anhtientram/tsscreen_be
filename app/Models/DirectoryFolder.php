<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectoryFolder extends Model
{
    protected $table = 'tb_dirs';

    protected $primaryKey = 'id_dir';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];
}
