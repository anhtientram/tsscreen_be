<?php

namespace Database\Seeders;

use App\Models\AppConfig;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    public function run(): void
    {
        AppConfig::putMany([
            'COMPANY_NAME' => 'TS Screen',
            'COMPANY_ADDRESS' => '',
            'HOTLINE' => '',
            'REPRESENTATIVE' => '',
            'EMAIL' => 'hello@example.com',
            'TAX_CODE' => '',
            'API_SERVER' => rtrim((string) config('app.url'), '/'),
            'GUIDE_LINK' => '',
            'ACTIVE_FLAG' => '1',
            'show_payment' => '1',
            'statement_date' => '1',
            'APPUSERANDROID_VERSION' => '',
            'APPUSERANDROID_BUILD_DATE' => '',
            'APPUSERANDROID_UPDATE_URL' => '',
            'APPUSERIOS_VERSION' => '',
            'APPUSERIOS_BUILD_DATE' => '',
            'APPUSERIOS_UPDATE_URL' => '',
            'APPTVBOX_VERSION' => '',
            'APPTVBOX_BUILD_DATE' => '',
            'APPTVBOX_UPDATE_URL' => '',
            'APPADMINANDROID_VERSION' => '',
            'APPADMINANDROID_BUILD_DATE' => '',
            'APPADMINANDROID_UPDATE_URL' => '',
            'APPADMINIOS_VERSION' => '',
            'APPADMINIOS_BUILD_DATE' => '',
            'APPADMINIOS_UPDATE_URL' => '',
            'VIETQR_BANK_BIN' => '',
            'VIETQR_ACCOUNT' => '',
            'VIETQR_ACCOUNT_NAME' => 'TS Screen',
        ]);
    }
}
