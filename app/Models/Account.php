<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class Account extends Model
{
    protected $table = 'tb_accounts';

    protected $primaryKey = 'account_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'last_MDF_date' => 'datetime',
        ];
    }

    public function setPasswordAttribute(string $value): void
    {
        if ($value !== '' && ! Hash::isHashed($value)) {
            $this->attributes['password'] = Hash::make($value);

            return;
        }

        $this->attributes['password'] = $value;
    }

    public function passwordMatches(string $submittedMd5): bool
    {
        return Hash::check($submittedMd5, $this->password);
    }

    public function toLegacyArray(): array
    {
        return [
            'account_id' => LegacyJson::str($this->account_id),
            'username' => LegacyJson::str($this->username),
            'email' => LegacyJson::str($this->email),
            'phone_number' => LegacyJson::str($this->phone_number),
            'user_type' => LegacyJson::str($this->user_type),
        ];
    }
}
