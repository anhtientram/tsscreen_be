<?php

namespace App\Filament\Pages;

use App\Filament\Schemas\AppConfigForm;
use App\Models\AppConfig;
use App\Services\TvBoxApkStorage;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

/**
 * @property-read Schema $form
 */
class ManageAppConfig extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Cấu hình hệ thống';

    protected static ?string $title = 'Cấu hình config6789';

    protected static ?int $navigationSort = 20;

    protected static ?string $slug = 'config';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->fillForm();

        if (session()->has('apk_upload_success')) {
            Notification::make()
                ->title('Upload APK thành công')
                ->body((string) session('apk_upload_success'))
                ->success()
                ->send();

            session()->forget('apk_upload_success');
        }
    }

    protected function fillForm(): void
    {
        $map = AppConfig::map();
        $data = [];

        foreach (AppConfigForm::keys() as $key) {
            $data[$key] = $map[$key] ?? '';
        }

        if ($data['API_SERVER'] === '') {
            $data['API_SERVER'] = rtrim((string) config('app.url'), '/');
        }

        if (TvBoxApkStorage::exists() && $data['APPTVBOX_UPDATE_URL'] === '') {
            $data['APPTVBOX_UPDATE_URL'] = TvBoxApkStorage::publicUrl($data['API_SERVER']);
        }

        $this->form->fill($data);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $apiServer = rtrim((string) ($data['API_SERVER'] ?? config('app.url')), '/');

        if (TvBoxApkStorage::exists()) {
            $data['APPTVBOX_UPDATE_URL'] = TvBoxApkStorage::publicUrl($apiServer);
        }

        AppConfig::putMany($data);

        Notification::make()
            ->title('Đã lưu cấu hình')
            ->body(TvBoxApkStorage::exists()
                ? 'APPTVBOX_UPDATE_URL: '.$data['APPTVBOX_UPDATE_URL']
                : 'App sẽ đọc giá trị mới tại GET /config6789.php')
            ->success()
            ->send();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return AppConfigForm::configure($schema);
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Cùng dữ liệu với config6789.php — Phone, TV và Admin app đọc khi khởi động.';
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            $this->getFormContentComponent(),
        ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make([
                    Action::make('save')
                        ->label('Lưu cấu hình')
                        ->submit('save')
                        ->keyBindings(['mod+s']),
                    Action::make('preview')
                        ->label('Xem config6789')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (): string => url('/config6789.php'))
                        ->openUrlInNewTab()
                        ->color('gray'),
                ])->key('form-actions'),
            ]);
    }
}
