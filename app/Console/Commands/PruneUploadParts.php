<?php

namespace App\Console\Commands;

use App\Models\UploadChunk;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneUploadParts extends Command
{
    protected $signature = 'uploads:prune-parts';

    protected $description = 'Xóa chunk upload dở (.part*) quá 24 giờ';

    public function handle(): int
    {
        $stale = UploadChunk::query()
            ->where('created_at', '<', now()->subDay())
            ->get();

        foreach ($stale as $part) {
            if ($part->part_path) {
                Storage::disk('uploads')->delete($part->part_path);
            }
            $part->delete();
        }

        $this->info('Pruned '.$stale->count().' parts');

        return self::SUCCESS;
    }
}
