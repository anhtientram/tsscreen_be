<?php

namespace App\Models;

use App\Support\LegacyJson;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $table = 'tb_orders';

    protected $primaryKey = 'paid_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function packet(): BelongsTo
    {
        return $this->belongsTo(Packet::class, 'packet_id', 'packet_id');
    }

    public function isActiveNow(): bool
    {
        if ($this->pay !== '1' || $this->deleted === 'y') {
            return false;
        }

        $now = now();
        $valid = $this->parseDate($this->valid_date);
        $expire = $this->parseDate($this->expire_date);

        if ($valid && $now->lt($valid)) {
            return false;
        }

        if ($expire && $now->gt($expire)) {
            return false;
        }

        return true;
    }

    public function computeExpireDate(string $validDate, ?Packet $packet = null): string
    {
        $valid = Carbon::parse($validDate);
        $payMonth = (int) ($this->pay_month ?: 0);

        if ($payMonth > 0) {
            return $valid->copy()->addMonths($payMonth)->format('Y-m-d');
        }

        $packet ??= $this->packet;
        $days = (int) ($this->day_qty ?: $packet?->day_qty ?: 0);
        $months = (int) ($this->month_qty ?: $packet?->month_qty ?: 0);
        $years = (int) ($this->year_qty ?: $packet?->year_qty ?: 0);

        return $valid->copy()->addDays($days)->addMonths($months)->addYears($years)->format('Y-m-d');
    }

    public function toAdminArray(): array
    {
        $customer = $this->relationLoaded('customer') ? $this->customer : $this->customer()->first();

        return [
            'paid_id' => LegacyJson::str($this->paid_id),
            'packet_id' => LegacyJson::str($this->packet_id),
            'packet_code' => LegacyJson::str($this->packet_code),
            'reg_number' => LegacyJson::str($this->reg_number),
            'name_packet' => LegacyJson::str($this->name_packet),
            'month_qty' => LegacyJson::str($this->month_qty),
            'price' => LegacyJson::str($this->price),
            'phone_number' => LegacyJson::str($customer?->phone_number),
            'customer_id' => LegacyJson::str($this->customer_id),
            'customer_name' => LegacyJson::str($customer?->customer_name),
            'pay' => LegacyJson::str($this->pay ?: '0'),
            'created_date' => LegacyJson::date($this->created_date),
            'deleted' => LegacyJson::str($this->deleted ?: 'n'),
            'register_date' => LegacyJson::str($this->register_date),
            'payment_date' => LegacyJson::str($this->payment_date),
            'valid_date' => LegacyJson::str($this->valid_date),
            'expire_date' => LegacyJson::str($this->expire_date),
            'type_pay' => LegacyJson::str($this->type_pay),
            'type' => LegacyJson::str($this->type),
            'picture' => LegacyJson::str($this->picture),
            'description' => LegacyJson::str($this->description),
            'detail' => LegacyJson::str($this->detail),
        ];
    }

    public function toMyPacketArray(): array
    {
        $packet = $this->relationLoaded('packet') ? $this->packet : $this->packet()->first();

        return [
            'paid_id' => LegacyJson::str($this->paid_id),
            'packet_code' => LegacyJson::str($this->packet_code),
            'reg_number' => LegacyJson::str($this->reg_number),
            'name_packet' => LegacyJson::str($this->name_packet),
            'price' => LegacyJson::str($this->price),
            'price_6_month' => LegacyJson::str($this->price_6_month ?: $packet?->price_6_month),
            'price_12_month' => LegacyJson::str($this->price_12_month ?: $packet?->price_12_month),
            'expire_date' => LegacyJson::str($this->expire_date),
            'day_qty' => LegacyJson::str($this->day_qty),
            'month_qty' => LegacyJson::str($this->month_qty),
            'year_qty' => LegacyJson::str($this->year_qty),
            'is_trial' => LegacyJson::str($this->is_trial ?: '0'),
            'is_business' => LegacyJson::str($this->is_business ?: '0'),
            'picture' => LegacyJson::str($this->picture),
            'description' => LegacyJson::str($this->description),
            'detail' => LegacyJson::str($this->detail),
            'customer_id' => LegacyJson::str($this->customer_id),
            'pay' => LegacyJson::str($this->pay ?: '0'),
            'created_date' => LegacyJson::date($this->created_date),
            'created_by' => LegacyJson::str($this->created_by),
            'last_MDF_by' => LegacyJson::str($this->last_MDF_by),
            'last_MDF_date' => LegacyJson::date($this->last_MDF_date),
            'deleted' => LegacyJson::str($this->deleted),
            'register_date' => LegacyJson::str($this->register_date),
            'payment_date' => LegacyJson::str($this->payment_date),
            'valid_date' => LegacyJson::str($this->valid_date),
            'type_pay' => LegacyJson::str($this->type_pay),
            'packet_id' => LegacyJson::str($this->packet_id),
            'type' => LegacyJson::str($this->type),
            'limit_capacity' => LegacyJson::str($this->limit_capacity ?: $packet?->limit_capacity ?: '0'),
            'limit_qty' => LegacyJson::str($this->limit_qty ?: $packet?->limit_qty ?: '0'),
            'payment_due_date' => LegacyJson::str($this->payment_due_date),
            'pay_month' => LegacyJson::str($this->pay_month),
        ];
    }

    private function parseDate(?string $value): ?Carbon
    {
        if ($value === null || $value === '' || str_contains($value, '0000-00-00')) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
