<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Customer extends Model
{
    protected $table = 'tb_users';

    protected $primaryKey = 'customer_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    protected $hidden = [];

    protected function casts(): array
    {
        return [
            'created_date' => 'datetime',
            'last_MDF_date' => 'datetime',
        ];
    }

    public static function boot(): void
    {
        parent::boot();

        static::creating(function (Customer $customer): void {
            if (empty($customer->customer_token)) {
                $customer->customer_token = Str::lower(Str::ulid()->toBase32());
            }
        });
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class, 'customer_id', 'customer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id', 'customer_id');
    }

    public function setPasswordAttribute(string $value): void
    {
        if ($value !== '' && ! Hash::isHashed($value)) {
            $this->attributes['password'] = Hash::make($value);

            return;
        }

        $this->attributes['password'] = $value;
    }

    public function passwordMatches(string $submitted): bool
    {
        if ($submitted === '') {
            return true;
        }

        return Hash::check($submitted, $this->password);
    }

    public function isActive(): bool
    {
        return $this->deleted !== 'y' && $this->status !== 'n';
    }

    public function toLegacyArray(?string $echoPassword = null): array
    {
        return [
            'customer_id' => LegacyJson::str($this->customer_id),
            'customer_name' => LegacyJson::str($this->customer_name),
            'address' => LegacyJson::str($this->address),
            'phone_number' => LegacyJson::str($this->phone_number),
            'email' => LegacyJson::str($this->email),
            'date_of_birth' => LegacyJson::str($this->date_of_birth),
            'sex' => LegacyJson::str($this->sex),
            'chu_tk' => LegacyJson::str($this->chu_tk),
            'stk' => LegacyJson::str($this->stk),
            'nganhang' => LegacyJson::str($this->nganhang),
            'chinhanh' => LegacyJson::str($this->chinhanh),
            'password' => $echoPassword ?? '',
            'customer_token' => LegacyJson::str($this->customer_token),
            'fcm_token' => LegacyJson::str($this->fcm_token),
            'created_date' => LegacyJson::date($this->created_date),
            'status' => LegacyJson::str($this->status ?: 'y'),
        ];
    }
}
