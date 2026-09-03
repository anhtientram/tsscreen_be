<?php

namespace App\Filament\Schemas;

use App\Services\TvBoxApkStorage;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

class AppConfigForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Công ty & liên hệ')
                    ->description('Hiển thị trên app và trang thanh toán')
                    ->icon('heroicon-o-building-office-2')
                    ->columns(2)
                    ->schema([
                        TextInput::make('COMPANY_NAME')->label('Tên công ty')->maxLength(255),
                        TextInput::make('HOTLINE')->label('Hotline')->tel(),
                        TextInput::make('EMAIL')->label('Email')->email(),
                        TextInput::make('REPRESENTATIVE')->label('Người đại diện'),
                        TextInput::make('TAX_CODE')->label('Mã số thuế'),
                        TextInput::make('COMPANY_ADDRESS')->label('Địa chỉ')->columnSpanFull(),
                        TextInput::make('GUIDE_LINK')->label('Link hướng dẫn')->url()->columnSpanFull(),
                    ]),
                Section::make('Máy chủ & hệ thống')
                    ->description('Trả về qua GET /config6789.php — 3 app đọc khi khởi động')
                    ->icon('heroicon-o-server-stack')
                    ->columns(2)
                    ->schema([
                        TextInput::make('API_SERVER')
                            ->label('API_SERVER')
                            ->url()
                            ->required()
                            ->helperText('URL backend, không có dấu / cuối'),
                        Select::make('ACTIVE_FLAG')
                            ->label('ACTIVE_FLAG')
                            ->options(['1' => 'Bật (1)', '0' => 'Tắt (0)'])
                            ->required(),
                        Toggle::make('show_payment')
                            ->label('Hiện thanh toán (show_payment)')
                            ->formatStateUsing(fn ($state) => ($state ?? '1') === '1')
                            ->dehydrateStateUsing(fn ($state) => $state ? '1' : '0'),
                        TextInput::make('statement_date')->label('Ngày sao kê (statement_date)'),
                    ]),
                Section::make('App Phone (Android / iOS)')
                    ->icon('heroicon-o-device-phone-mobile')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('APPUSERANDROID_VERSION')->label('Android version'),
                        TextInput::make('APPUSERANDROID_BUILD_DATE')->label('Android build date'),
                        TextInput::make('APPUSERANDROID_UPDATE_URL')->label('Android update URL')->url(),
                        TextInput::make('APPUSERIOS_VERSION')->label('iOS version'),
                        TextInput::make('APPUSERIOS_BUILD_DATE')->label('iOS build date'),
                        TextInput::make('APPUSERIOS_UPDATE_URL')->label('iOS update URL')->url(),
                    ]),
                Section::make('App TV Box')
                    ->description('Upload APK — link tải tự gán vào APPTVBOX_UPDATE_URL (config6789)')
                    ->icon('heroicon-o-tv')
                    ->columns(2)
                    ->schema([
                        TextInput::make('APPTVBOX_VERSION')
                            ->label('Version')
                            ->columnSpan(1),
                        TextInput::make('APPTVBOX_BUILD_DATE')
                            ->label('Build date')
                            ->columnSpan(1),
                        Placeholder::make('tvbox_apk_status')
                            ->label('APK đang host')
                            ->content(function (Get $get): HtmlString|string {
                                if (! TvBoxApkStorage::exists()) {
                                    return 'Chưa có file APK trên server.';
                                }

                                $url = e(TvBoxApkStorage::publicUrl($get('API_SERVER') ?: config('app.url')));

                                return new HtmlString(
                                    'Đang có <strong>tvbox.apk</strong> ('.e(TvBoxApkStorage::formattedSize()).') — '
                                    .'<a href="'.$url.'" target="_blank" rel="noopener" class="text-primary-600 underline dark:text-primary-400">Tải thử</a>'
                                );
                            })
                            ->columnSpanFull(),
                        View::make('filament.forms.tvbox-apk-upload')
                            ->columnSpanFull(),
                        TextInput::make('APPTVBOX_UPDATE_URL')
                            ->label('Link tải (APPTVBOX_UPDATE_URL)')
                            ->readOnly()
                            ->helperText('Tự cập nhật sau khi upload APK, hoặc dùng API_SERVER + /apk/tvbox.apk')
                            ->columnSpanFull(),
                    ]),
                Section::make('App Admin (Android / iOS)')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextInput::make('APPADMINANDROID_VERSION')->label('Android version'),
                        TextInput::make('APPADMINANDROID_BUILD_DATE')->label('Android build date'),
                        TextInput::make('APPADMINANDROID_UPDATE_URL')->label('Android update URL')->url(),
                        TextInput::make('APPADMINIOS_VERSION')->label('iOS version'),
                        TextInput::make('APPADMINIOS_BUILD_DATE')->label('iOS build date'),
                        TextInput::make('APPADMINIOS_UPDATE_URL')->label('iOS update URL')->url(),
                    ]),
                Section::make('VietQR (server-side)')
                    ->description('Không trả trong config6789; dùng khi tạo QR thanh toán')
                    ->icon('heroicon-o-qr-code')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextInput::make('VIETQR_BANK_BIN')->label('Bank BIN'),
                        TextInput::make('VIETQR_ACCOUNT')->label('Số tài khoản'),
                        TextInput::make('VIETQR_ACCOUNT_NAME')->label('Tên chủ TK')->columnSpanFull(),
                    ]),
            ]);
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return [
            'COMPANY_NAME', 'COMPANY_ADDRESS', 'HOTLINE', 'REPRESENTATIVE', 'EMAIL', 'TAX_CODE',
            'API_SERVER', 'GUIDE_LINK', 'ACTIVE_FLAG', 'show_payment', 'statement_date',
            'APPUSERANDROID_VERSION', 'APPUSERANDROID_BUILD_DATE', 'APPUSERANDROID_UPDATE_URL',
            'APPUSERIOS_VERSION', 'APPUSERIOS_BUILD_DATE', 'APPUSERIOS_UPDATE_URL',
            'APPTVBOX_VERSION', 'APPTVBOX_BUILD_DATE', 'APPTVBOX_UPDATE_URL',
            'APPADMINANDROID_VERSION', 'APPADMINANDROID_BUILD_DATE', 'APPADMINANDROID_UPDATE_URL',
            'APPADMINIOS_VERSION', 'APPADMINIOS_BUILD_DATE', 'APPADMINIOS_UPDATE_URL',
            'VIETQR_BANK_BIN', 'VIETQR_ACCOUNT', 'VIETQR_ACCOUNT_NAME',
        ];
    }
}
