<?php

namespace App\Console\Commands;

use App\Models\CollectionItem;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class MoveCollectionItemImagesToS3 extends Command
{
    protected $signature = 'collection-items:move-to-s3
        {--delete : Delete files from the source disk once they are verified on the target disk}
        {--dry-run : Report what would happen without copying or deleting anything}
        {--source=public : Disk to move files from}
        {--target=s3 : Disk to move files to}';

    protected $description = 'Copy collection item images to S3, verify them, and optionally delete the originals';

    public function handle(): int
    {
        $source = Storage::disk($this->option('source'));
        $target = Storage::disk($this->option('target'));
        $dryRun = (bool) $this->option('dry-run');
        $delete = (bool) $this->option('delete');

        if ($this->option('source') === $this->option('target')) {
            $this->error('Source and target disks must be different.');

            return self::FAILURE;
        }

        $query = CollectionItem::query()->whereNotNull('image_path');

        $stats = ['copied' => 0, 'already_on_target' => 0, 'deleted' => 0, 'missing' => 0, 'failed' => 0];
        $problems = [];

        $bar = $this->output->createProgressBar($query->count());
        $bar->start();

        $query->chunkById(100, function ($items) use ($source, $target, $dryRun, $delete, &$stats, &$problems, $bar): void {
            foreach ($items as $item) {
                $path = $item->image_path;

                try {
                    $result = $this->migrate($source, $target, $path, $dryRun, $delete);

                    $stats[$result['status']]++;
                    $stats['deleted'] += $result['deleted'] ? 1 : 0;

                    if ($result['status'] === 'missing') {
                        $problems[] = "Item {$item->id}: {$path} not found on either disk";
                    }
                } catch (Throwable $e) {
                    $stats['failed']++;
                    $problems[] = "Item {$item->id}: {$path} - {$e->getMessage()}";
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Result', 'Count'],
            collect($stats)->map(fn (int $count, string $label): array => [$label, $count])->values()->all(),
        );

        foreach ($problems as $problem) {
            $this->warn($problem);
        }

        if ($dryRun) {
            $this->info('Dry run: nothing was copied or deleted.');
        } elseif (! $delete) {
            $this->info('Files copied and verified. Re-run with --delete to remove them from the source disk.');
        }

        return $stats['failed'] > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array{status: 'copied'|'already_on_target'|'missing', deleted: bool}
     */
    private function migrate(Filesystem $source, Filesystem $target, string $path, bool $dryRun, bool $delete): array
    {
        $onSource = $source->exists($path);
        $onTarget = $target->exists($path);

        if (! $onSource && ! $onTarget) {
            return ['status' => 'missing', 'deleted' => false];
        }

        // Nothing left on the source disk, so there is nothing to copy or delete.
        if (! $onSource) {
            return ['status' => 'already_on_target', 'deleted' => false];
        }

        $status = 'already_on_target';

        if (! $onTarget) {
            $status = 'copied';

            if ($dryRun) {
                return ['status' => $status, 'deleted' => false];
            }

            $this->copy($source, $target, $path);
        }

        // Never delete unless the target copy exists and matches the original's size.
        $this->verify($source, $target, $path);

        $deleted = false;

        if ($delete && ! $dryRun) {
            $source->delete($path);
            $deleted = true;
        }

        return ['status' => $status, 'deleted' => $deleted];
    }

    private function copy(Filesystem $source, Filesystem $target, string $path): void
    {
        $stream = $source->readStream($path);

        if (! is_resource($stream)) {
            throw new RuntimeException('Could not open source file for reading.');
        }

        try {
            $target->writeStream($path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function verify(Filesystem $source, Filesystem $target, string $path): void
    {
        if (! $target->exists($path)) {
            throw new RuntimeException('File is missing from the target disk after copy.');
        }

        $sourceSize = $source->size($path);
        $targetSize = $target->size($path);

        if ($sourceSize !== $targetSize) {
            throw new RuntimeException("Size mismatch (source {$sourceSize} bytes, target {$targetSize} bytes).");
        }
    }
}