<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'tb_transactions';

    protected $primaryKey = 'transaction_id';

    public $timestamps = false;

    protected $guarded = [];
}
