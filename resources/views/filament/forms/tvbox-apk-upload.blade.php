<form
    action="{{ route('admin.apk.upload') }}"
    method="POST"
    enctype="multipart/form-data"
    class="space-y-3"
>
    @csrf

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <input
            type="file"
            name="apk"
            accept=".apk,application/vnd.android.package-archive"
            required
            class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-white text-sm text-gray-900 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        />

        <x-filament::button type="submit" color="primary">
            Upload APK
        </x-filament::button>
    </div>

    @error('apk')
        <p class="text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
    @enderror

    <p class="text-sm text-gray-500 dark:text-gray-400">
        File .apk tối đa 110MB — upload trực tiếp lên server (không qua Livewire).
    </p>
</form>
