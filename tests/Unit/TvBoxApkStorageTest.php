<?php

namespace Tests\Unit;

use App\Services\TvBoxApkStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TvBoxApkStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk(TvBoxApkStorage::DISK)->delete(TvBoxApkStorage::FILENAME);
    }

    public function test_store_and_public_url(): void
    {
        $file = UploadedFile::fake()->create('tvbox.apk', 128, 'application/vnd.android.package-archive');

        $url = TvBoxApkStorage::store($file);

        $this->assertTrue(TvBoxApkStorage::exists());
        $this->assertStringEndsWith('/apk/tvbox.apk', $url);
        $this->assertSame('https://api.example.com/apk/tvbox.apk', TvBoxApkStorage::publicUrl('https://api.example.com'));
    }
}
