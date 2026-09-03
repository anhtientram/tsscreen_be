<?php

return [

    'class_namespace' => 'App\\Livewire',

    'view_path' => resource_path('views/livewire'),

    'layout' => 'components.layouts.app',

    'lazy_placeholder' => null,

    'temporary_file_upload' => [
        // Cùng volume /data/uploads với APK — copy nhanh hơn khi Lưu.
        'disk' => env('LIVEWIRE_TMP_DISK', 'uploads'),
        'rules' => ['file', 'max:102400'], // 100MB — APK TV thường > 12MB (mặc định Livewire 12MB)
        'directory' => env('LIVEWIRE_TMP_DIR', 'uploads/livewire-tmp'),
        'middleware' => null,
        'preview_mimes' => [
            'png', 'gif', 'bmp', 'svg', 'wav', 'mp4',
            'mov', 'avi', 'wmv', 'mp3', 'm4a',
            'jpg', 'jpeg', 'mpga', 'webp', 'wma',
            'apk',
        ],
        'max_upload_time' => 10, // phút (Livewire mặc định 5)
        'cleanup' => true,
    ],

    'render_on_redirect' => false,

    'legacy_model_binding' => false,

    'inject_assets' => true,

    'navigate' => [
        'show_progress_bar' => true,
        'progress_bar_color' => '#2299dd',
    ],

    'inject_morph_markers' => true,

    'smart_wire_keys' => false,

    'pagination_theme' => 'tailwind',

    'release_token' => 'a',
];
