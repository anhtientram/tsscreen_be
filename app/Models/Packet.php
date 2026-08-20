<?php

namespace App\Models;

use App\Support\LegacyJson;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Packet extends Model
{
    protected $table = 'tb_packets';

    protected $primaryKey = 'packet_id';

    const CREATED_AT = 'created_date';

    const UPDATED_AT = 'last_MDF_date';

    protected $guarded = [];

    public function toLegacyArray(): array
    {
        return [
            'packet_id' => LegacyJson::str($this->packet_id),
            'name_packet' => LegacyJson::str($this->name_packet),
            'price' => LegacyJson::str($this->price),
            'price_6_month' => LegacyJson::str($this->price_6_month),
            'price_12_month' => LegacyJson::str($this->price_12_month),
            'day_qty' => LegacyJson::str($this->day_qty ?: '0'),
            'month_qty' => LegacyJson::str($this->month_qty ?: '0'),
            'year_qty' => LegacyJson::str($this->year_qty ?: '0'),
            'is_trial' => LegacyJson::str($this->is_trial ?: '0'),
            'is_business' => LegacyJson::str($this->is_business ?: '0'),
            'detail' => LegacyJson::str($this->detail),
            'description' => LegacyJson::str($this->description),
            'picture' => LegacyJson::str($this->picture),
            'expire_date' => LegacyJson::str($this->expire_date),
            'limit_capacity' => LegacyJson::str($this->limit_capacity ?: '0'),
            'limit_qty' => LegacyJson::str($this->limit_qty ?: '0'),
        ];
    }

    public static function fillFromRequest(Request $request, ?self $packet = null): self
    {
        $packet ??= new self;
        $packet->fill([
            'name_packet' => $request->input('name_packet', $packet->name_packet),
            'price' => $request->input('price', $packet->price),
            'price_6_month' => $request->input('price_6_month', $packet->price_6_month),
            'price_12_month' => $request->input('price_12_month', $packet->price_12_month),
            'month_qty' => $request->input('month_qty', $packet->month_qty ?: '0'),
            'day_qty' => $request->input('day_qty', $packet->day_qty ?: '0'),
            'year_qty' => $request->input('year_qty', $packet->year_qty ?: '0'),
            'picture' => $request->input('picture', $packet->picture ?: ''),
            'detail' => $request->input('detail', $packet->detail),
            'description' => $request->input('description', $packet->description),
            'limit_qty' => $request->input('limit_qty', $packet->limit_qty ?: '0'),
            'limit_capacity' => $request->input('limit_capacity', $packet->limit_capacity ?: '0'),
            'account_id' => $request->input('account_id', $packet->account_id),
            'is_trial' => $request->input('is_trial', $packet->is_trial ?: '0'),
            'is_business' => $request->input('is_business', $packet->is_business ?: '0'),
            'deleted' => $packet->deleted ?: 'n',
        ]);

        return $packet;
    }
}
