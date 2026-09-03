<?php

namespace App\Models;

use App\Support\LegacyJson;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class Account extends Authenticatable implements FilamentUser, HasName
{
    protected $table = 'tb_accounts';

    protected $primaryKey = 'account_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'fcm_token',
    ];

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

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->deleted !== 'y';
    }

    public function getFilamentName(): string
    {
        return (string) ($this->username ?: $this->email ?: 'Admin');
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
