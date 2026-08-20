<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountNotification extends Model
{
    protected $table = 'tb_account_notifications';

    protected $primaryKey = 'id_notify';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = null;

    protected $guarded = [];
}
