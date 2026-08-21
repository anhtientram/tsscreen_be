<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppConfig extends Model
{
    protected $table = 'tb_configs';

    protected $guarded = [];

    public $timestamps = false;

    public static function map(): array
    {
        return static::query()->pluck('config_value', 'config_key')->all();
    }

    public static function putMany(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(
                ['config_key' => $key],
                ['config_value' => $value]
            );
        }

        Cache::forget('tb_configs_map');
    }

    public static function toLegacyPayload(): array
    {
        $c = static::map();

        return [
            'COMPANY_NAME' => $c['COMPANY_NAME'] ?? '',
            'COMPANY_ADDRESS' => $c['COMPANY_ADDRESS'] ?? '',
            'HOTLINE' => $c['HOTLINE'] ?? '',
            'REPRESENTATIVE' => $c['REPRESENTATIVE'] ?? '',
            'EMAIL' => $c['EMAIL'] ?? '',
            'TAX_CODE' => $c['TAX_CODE'] ?? '',
            'API_SERVER' => $c['API_SERVER'] ?? rtrim((string) config('app.url'), '/'),
            'GUIDE_LINK' => $c['GUIDE_LINK'] ?? '',
            'ACTIVE_FLAG' => isset($c['ACTIVE_FLAG']) ? (int) $c['ACTIVE_FLAG'] : 1,
            'show_payment' => $c['show_payment'] ?? '1',
            'statement_date' => $c['statement_date'] ?? '',
            'APPUSERANDROID_VERSION' => $c['APPUSERANDROID_VERSION'] ?? '',
            'APPUSERANDROID_BUILD_DATE' => $c['APPUSERANDROID_BUILD_DATE'] ?? '',
            'APPUSERANDROID_UPDATE_URL' => $c['APPUSERANDROID_UPDATE_URL'] ?? '',
            'APPUSERIOS_VERSION' => $c['APPUSERIOS_VERSION'] ?? '',
            'APPUSERIOS_BUILD_DATE' => $c['APPUSERIOS_BUILD_DATE'] ?? '',
            'APPUSERIOS_UPDATE_URL' => $c['APPUSERIOS_UPDATE_URL'] ?? '',
            'APPTVBOX_VERSION' => $c['APPTVBOX_VERSION'] ?? '',
            'APPTVBOX_BUILD_DATE' => $c['APPTVBOX_BUILD_DATE'] ?? '',
            'APPTVBOX_UPDATE_URL' => $c['APPTVBOX_UPDATE_URL'] ?? '',
            'APPADMINANDROID_VERSION' => $c['APPADMINANDROID_VERSION'] ?? '',
            'APPADMINANDROID_BUILD_DATE' => $c['APPADMINANDROID_BUILD_DATE'] ?? '',
            'APPADMINANDROID_UPDATE_URL' => $c['APPADMINANDROID_UPDATE_URL'] ?? '',
            'APPADMINIOS_VERSION' => $c['APPADMINIOS_VERSION'] ?? '',
            'APPADMINIOS_BUILD_DATE' => $c['APPADMINIOS_BUILD_DATE'] ?? '',
            'APPADMINIOS_UPDATE_URL' => $c['APPADMINIOS_UPDATE_URL'] ?? '',
        ];
    }
}
