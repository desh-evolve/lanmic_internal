<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sage300Service;

class WarmSage300ItemsCache extends Command
{
    protected $signature   = 'sage300:warm-items {--force : Deactivate all items before syncing}';
    protected $description = 'Sync all items from Sage 300 into the local database (upsert new/changed, deactivate removed).';

    public function handle(Sage300Service $sage300): int
    {
        if ($this->option('force')) {
            $sage300->clearItemsCache();
            $this->info('All items marked inactive — starting fresh sync.');
        }

        $this->info('Syncing items from Sage 300 (this may take a moment)...');

        $stats = $sage300->syncItems();

        $this->info(sprintf(
            'Done — %d total active | %d added | %d updated | %d deactivated',
            $stats['total'],
            $stats['added'],
            $stats['updated'],
            $stats['deactivated'],
        ));

        return Command::SUCCESS;
    }
}
