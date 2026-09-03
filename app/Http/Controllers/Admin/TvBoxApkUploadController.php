<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use App\Services\TvBoxApkStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TvBoxApkUploadController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'apk' => ['required', 'file', 'max:112640'],
        ], [
            'apk.required' => 'Chọn file APK.',
            'apk.file' => 'File không hợp lệ.',
            'apk.max' => 'APK tối đa 110MB.',
        ]);

        /** @var \Illuminate\Http\UploadedFile $file */
        $file = $validated['apk'];

        if (strtolower($file->getClientOriginalExtension()) !== 'apk') {
            return back()->withErrors(['apk' => 'File phải có đuôi .apk']);
        }

        TvBoxApkStorage::store($file);

        $map = AppConfig::map();
        $apiServer = rtrim((string) ($map['API_SERVER'] ?? config('app.url')), '/');
        $url = TvBoxApkStorage::publicUrl($apiServer);

        AppConfig::putMany(['APPTVBOX_UPDATE_URL' => $url]);

        return redirect()
            ->to('/admin/config')
            ->with('apk_upload_success', 'Đã upload tvbox.apk — '.$url);
    }
}
