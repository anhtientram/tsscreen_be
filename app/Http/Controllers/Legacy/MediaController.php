<?php

namespace App\Http\Controllers\Legacy;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ResourceFile;
use App\Models\UploadChunk;
use App\OpenApi\AppTags;
use App\Services\PacketQuota;
use App\Support\DiskWatermark;
use App\Support\LegacyJson;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaController extends Controller
{
    private const MAX_REQUEST_BYTES = 110 * 1024 * 1024;

    private const MIME_WHITELIST = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
        'video/mp4',
        'video/quicktime',
        'video/webm',
    ];

    private const EXT_WHITELIST = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'mov', 'webm'];

    #[OA\Post(path: '/home/checkdir_customer', summary: 'Thư mục media theo customer_token', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function checkDir(Request $request)
    {
        $token = $this->token($request);
        $exists = $token !== '' && Storage::disk('uploads')->exists('uploads/'.$token);

        return LegacyJson::send(['status' => $exists ? 1 : 0, 'msg' => $exists ? 'OK' : 'not found']);
    }

    #[OA\Post(path: '/home/createdir_customer', summary: 'Tạo folder uploads/{token}', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function createDir(Request $request)
    {
        $token = $this->token($request);
        if ($token === '') {
            return LegacyJson::send(['status' => 0, 'msg' => 'Thiếu name_dir']);
        }

        Storage::disk('uploads')->makeDirectory('uploads/'.$token);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/getfiles_customer', summary: 'file_list — bỏ .part*', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function files(Request $request)
    {
        $token = $this->token($request);
        $list = ResourceFile::alive()
            ->where('name_dir', $token)
            ->orderByDesc('id')
            ->get()
            ->filter(fn (ResourceFile $f) => ! preg_match('/\.part\d+$/', (string) $f->path) && ! preg_match('/\.part\d+$/', (string) $f->name))
            ->map(fn (ResourceFile $f) => $f->toLegacyArray())
            ->values()
            ->all();

        return LegacyJson::send(['file_list' => $list]);
    }

    #[OA\Post(path: '/home/getsizeofdir_customer', summary: 'totalsize string — quota từ tb_resources', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function dirSize(Request $request)
    {
        $token = $this->token($request);
        $customer = $this->customerByToken($token);
        $total = $customer
            ? PacketQuota::usedCapacity($customer->customer_id)
            : (int) ResourceFile::alive()->where('name_dir', $token)->sum('file_size');

        return LegacyJson::send(['totalsize' => LegacyJson::str($total)]);
    }

    #[OA\Post(path: '/home/uploadfile_customer', summary: 'Upload nhỏ ≤200MB client; server max 110MB/request', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function upload(Request $request)
    {
        return $this->withUploadLock($request, function () use ($request) {
            $file = $request->file('fileupload');
            if (! $file instanceof UploadedFile) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Thiếu file']);
            }

            $guard = $this->guardUpload($request, $file);
            if ($guard !== null) {
                return $guard;
            }

            $token = $this->token($request);
            $customer = $this->resolveCustomer($request, $token);
            $name = $this->safeName($file->getClientOriginalName());
            $this->ensureDir($token);

            $relative = 'uploads/'.$token.'/'.$name;
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('uploads')->put($relative, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            $resource = $this->storeResource($customer, $token, $name, $file->getSize(), $file->getMimeType() ?: $file->getClientMimeType());

            return LegacyJson::send([
                'status' => 1,
                'msg' => 'OK',
                'path_file' => $resource->toLegacyArray(),
            ]);
        });
    }

    #[OA\Post(path: '/home/uploadfile_customer_large', summary: 'Chunk 100MB; ghép khi đủ total_chunks', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function uploadLarge(Request $request)
    {
        return $this->withUploadLock($request, function () use ($request) {
            $file = $request->file('fileupload');
            if (! $file instanceof UploadedFile) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Thiếu file']);
            }

            $token = $this->token($request);
            $filename = $this->safeName((string) $request->input('filename', $file->getClientOriginalName()));
            $chunkIndex = (int) $request->input('chunk_index');
            $totalChunks = (int) $request->input('total_chunks');
            $customer = $this->resolveCustomer($request, $token);

            if (DiskWatermark::isFull()) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Ổ đĩa server đầy']);
            }
            if ($file->getSize() > self::MAX_REQUEST_BYTES) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Chunk quá lớn']);
            }
            if (! $this->isAllowedName($filename)) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Định dạng không hỗ trợ']);
            }

            $this->ensureDir($token);
            $partName = $filename.'.part'.$chunkIndex;
            $partRelative = 'uploads/'.$token.'/'.$partName;
            $stream = fopen($file->getRealPath(), 'r');
            Storage::disk('uploads')->put($partRelative, $stream);
            if (is_resource($stream)) {
                fclose($stream);
            }

            UploadChunk::query()->updateOrCreate(
                [
                    'name_dir' => $token,
                    'filename' => $filename,
                    'chunk_index' => $chunkIndex,
                ],
                [
                    'customer_id' => $customer?->customer_id,
                    'total_chunks' => $totalChunks,
                    'part_path' => $partRelative,
                    'created_at' => now(),
                ]
            );

            $have = UploadChunk::query()
                ->where('name_dir', $token)
                ->where('filename', $filename)
                ->count();

            if ($have < $totalChunks) {
                return LegacyJson::send(['status' => 1, 'msg' => 'chunk ok']);
            }

            $assembled = $this->assemble($token, $filename, $totalChunks, $customer);
            if ($assembled === null) {
                return LegacyJson::send(['status' => 0, 'msg' => 'Ghép file thất bại hoặc hết dung lượng gói']);
            }

            return LegacyJson::send([
                'status' => 1,
                'msg' => 'OK',
                'path_file' => $assembled->toLegacyArray(),
            ]);
        });
    }

    #[OA\Post(path: '/home/deletefile_customer', summary: 'Xóa file media', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function delete(Request $request)
    {
        $token = $this->token($request);
        $name = $this->safeName((string) $request->input('name_file'));
        $row = ResourceFile::alive()->where('name_dir', $token)->where('name', $name)->first();
        if ($row) {
            $row->deleted = 'y';
            $row->save();
        }
        Storage::disk('uploads')->delete('uploads/'.$token.'/'.$name);

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Post(path: '/home/cancelUpload', summary: 'Xóa .part* dở', tags: [AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'legacy JSON')])]
    public function cancel(Request $request)
    {
        $token = $this->token($request);
        $filename = $this->safeName((string) $request->input('filename'));
        $parts = UploadChunk::query()
            ->where('name_dir', $token)
            ->where('filename', $filename)
            ->get();

        foreach ($parts as $part) {
            if ($part->part_path) {
                Storage::disk('uploads')->delete($part->part_path);
            }
            $part->delete();
        }

        return LegacyJson::send(['status' => 1, 'msg' => 'OK']);
    }

    #[OA\Get(path: '/uploads/{token}/{filename}', summary: 'GET + HEAD media (app HEAD lấy content-length)', tags: [AppTags::PROJECTOR, AppTags::CUSTOMER], responses: [new OA\Response(response: 200, description: 'file / HEAD length')])]
    public function serve(Request $request, string $token, string $filename): BinaryFileResponse|Response
    {
        $filename = $this->safeName(urldecode($filename));
        $relative = 'uploads/'.$token.'/'.$filename;
        $full = Storage::disk('uploads')->path($relative);
        if (! is_file($full)) {
            abort(404);
        }

        $headers = [
            'Accept-Ranges' => 'bytes',
            'Content-Length' => (string) filesize($full),
            'Content-Type' => mime_content_type($full) ?: 'application/octet-stream',
        ];

        if ($request->isMethod('HEAD')) {
            return response('', 200, $headers);
        }

        return response()->file($full, $headers);
    }

    private function withUploadLock(Request $request, \Closure $then)
    {
        $token = $this->token($request);
        $customer = $this->resolveCustomer($request, $token);
        $key = 'upload_inflight:'.($customer?->customer_id ?: $token);
        $count = (int) Cache::get($key, 0);
        if ($count >= 2) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Đang có quá nhiều upload']);
        }

        Cache::put($key, $count + 1, 300);
        try {
            return $then();
        } finally {
            $left = (int) Cache::get($key, 1) - 1;
            if ($left <= 0) {
                Cache::forget($key);
            } else {
                Cache::put($key, $left, 300);
            }
        }
    }

    private function guardUpload(Request $request, UploadedFile $file)
    {
        if (DiskWatermark::isFull()) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Ổ đĩa server đầy']);
        }
        if ($file->getSize() > self::MAX_REQUEST_BYTES) {
            return LegacyJson::send(['status' => 0, 'msg' => 'File vượt giới hạn request']);
        }

        $mime = $file->getMimeType() ?: $file->getClientMimeType();
        $name = $this->safeName($file->getClientOriginalName());
        if (! $this->isAllowedName($name)) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Định dạng không hỗ trợ']);
        }
        if ($mime && ! in_array($mime, self::MIME_WHITELIST, true) && ! in_array($mime, ['application/octet-stream', 'application/mp4'], true)) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Định dạng không hỗ trợ']);
        }

        $token = $this->token($request);
        $customer = $this->resolveCustomer($request, $token);
        if (! $customer) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Không tìm thấy khách hàng']);
        }
        if (! PacketQuota::canAddBytes($customer->customer_id, (int) $file->getSize())) {
            return LegacyJson::send(['status' => 0, 'msg' => 'Hết dung lượng gói']);
        }

        return null;
    }

    private function assemble(string $token, string $filename, int $totalChunks, ?Customer $customer): ?ResourceFile
    {
        $relative = 'uploads/'.$token.'/'.$filename;
        $full = Storage::disk('uploads')->path($relative);
        $dir = dirname($full);
        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $out = fopen($full, 'w');
        if ($out === false) {
            return null;
        }

        $size = 0;
        for ($i = 1; $i <= $totalChunks; $i++) {
            $partRelative = 'uploads/'.$token.'/'.$filename.'.part'.$i;
            $partFull = Storage::disk('uploads')->path($partRelative);
            if (! is_file($partFull)) {
                fclose($out);
                @unlink($full);

                return null;
            }
            $in = fopen($partFull, 'r');
            stream_copy_to_stream($in, $out);
            fclose($in);
            $size += filesize($partFull);
        }
        fclose($out);

        if ($customer && ! PacketQuota::canAddBytes($customer->customer_id, $size)) {
            @unlink($full);
            $this->deleteParts($token, $filename);

            return null;
        }

        for ($i = 1; $i <= $totalChunks; $i++) {
            Storage::disk('uploads')->delete('uploads/'.$token.'/'.$filename.'.part'.$i);
        }
        UploadChunk::query()->where('name_dir', $token)->where('filename', $filename)->delete();

        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'mp4' => 'video/mp4',
            'mov' => 'video/quicktime',
            'webm' => 'video/webm',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            default => 'image/jpeg',
        };

        return $this->storeResource($customer, $token, $filename, $size, $mime);
    }

    private function deleteParts(string $token, string $filename): void
    {
        $parts = UploadChunk::query()->where('name_dir', $token)->where('filename', $filename)->get();
        foreach ($parts as $part) {
            if ($part->part_path) {
                Storage::disk('uploads')->delete($part->part_path);
            }
            $part->delete();
        }
    }

    private function storeResource(?Customer $customer, string $token, string $name, int $size, ?string $mime): ResourceFile
    {
        return ResourceFile::query()->create([
            'customer_id' => $customer?->customer_id,
            'name_dir' => $token,
            'name' => $name,
            'path' => './uploads/'.$token.'/'.$name,
            'file_size' => $size,
            'file_type' => $mime ?: '',
            'creation_time' => now(),
            'modification_time' => now(),
            'deleted' => 'n',
        ]);
    }

    private function token(Request $request): string
    {
        return $this->safeName((string) $request->input('name_dir'));
    }

    private function customerByToken(string $token): ?Customer
    {
        if ($token === '') {
            return null;
        }

        return Customer::query()->where('customer_token', $token)->first();
    }

    private function resolveCustomer(Request $request, string $token): ?Customer
    {
        $id = $request->input('customer_id');
        if ($id) {
            return Customer::query()->where('customer_id', $id)->first();
        }

        return $this->customerByToken($token);
    }

    private function ensureDir(string $token): void
    {
        Storage::disk('uploads')->makeDirectory('uploads/'.$token);
    }

    private function safeName(string $name): string
    {
        $name = basename(str_replace(["\0", '..'], '', $name));

        return $name;
    }

    private function isAllowedName(string $name): bool
    {
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        return in_array($ext, self::EXT_WHITELIST, true);
    }
}
