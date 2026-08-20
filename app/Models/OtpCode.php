<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $table = 'tb_otp_codes';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function isValid(string $code): bool
    {
        return $this->used_at === null
            && $this->code_authen === $code
            && $this->expires_at?->isFuture();
    }
}
