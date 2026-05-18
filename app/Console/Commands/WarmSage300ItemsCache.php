<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Sage300Service;

class WarmSage300ItemsCache extends Command
{
    protected $signature   = 'sage300:warm-items {--force : Clear existing cache before fetching}';
    protected $description = 'Fetch all items from Sage 300 and store them in the indefinite cache.';

    public function handle(Sage300Service $sage300): int
    {
        if ($this->option('force')) {
            $sage300->clearItemsCache();
            $this->info('Existing cache cleared.');
        }

        $this->info('Fetching all items from Sage 300 (this may take a moment)...');

        $items = $sage300->fetchAndCacheAllItems();

        $this->info('Done — ' . count($items) . ' items cached.');

        return Command::SUCCESS;
    }
}
