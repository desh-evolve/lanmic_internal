<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sage300Service;
use Illuminate\Support\Facades\Log;
use Throwable;

class WarmSage300ItemsCache extends Command
{
    protected $signature   = 'sage300:warm-items {--skip=0 : Start at this Sage item offset}';
    protected $description = 'Sync all items from Sage 300 into the local database (upsert new/changed, deactivate removed).';

    public function handle(Sage300Service $sage300): int
    {
        $startOffset = filter_var($this->option('skip'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($startOffset === false) {
            $this->error('--skip must be a non-negative integer.');

            return Command::INVALID;
        }

        if ($startOffset > 0) {
            $this->info('Resuming at Sage offset '.number_format($startOffset).'. No items will be deactivated.');
        }

        $this->info('Syncing items from Sage 300 (this may take a moment)...');

        try {
            $startedAt = microtime(true);
            $stats = $sage300->syncItems(function (int $processed, int $added, int $updated) use ($startedAt): void {
                $this->line(sprintf(
                    '[%s] %s items saved (%s added, %s updated) — %ds elapsed',
                    now()->format('H:i:s'),
                    number_format($processed),
                    number_format($added),
                    number_format($updated),
                    (int) (microtime(true) - $startedAt),
                ));
            }, $startOffset);

            $this->info(sprintf(
                'Done — %d total active | %d added | %d updated | %d deactivated',
                $stats['total'],
                $stats['added'],
                $stats['updated'],
                $stats['deactivated'],
            ));

            return Command::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Sage 300 item synchronization failed', [
                'error' => $exception->getMessage(),
            ]);
            $this->error('Item synchronization failed. Check storage/logs/laravel.log.');

            return Command::FAILURE;
        }
    }
}
