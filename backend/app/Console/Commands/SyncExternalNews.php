<?php

namespace App\Console\Commands;

use App\Models\NewsSource;
use App\Services\News\FeedFetcherService;
use Illuminate\Console\Command;

class SyncExternalNews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'news:sync {--source=all : Source slug or "all"} {--force : Ignore sync_interval_hours}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and curate external news from RSS feeds';

    /**
     * Execute the console command.
     */
    public function handle(FeedFetcherService $fetcherService)
    {
        $sourceSlug = $this->option('source');
        $force = $this->option('force');

        $this->info("Starting external news sync...");

        if ($sourceSlug !== 'all') {
            $source = NewsSource::where('slug', $sourceSlug)->where('is_active', true)->first();
            if (!$source) {
                $this->error("Active source with slug '{$sourceSlug}' not found.");
                return 1;
            }

            $this->info("Syncing source: {$source->name}");
            $log = $fetcherService->syncSource($source, $force);
            $this->displayLog($log);
        } else {
            $sources = NewsSource::where('is_active', true)->get();
            if ($sources->isEmpty()) {
                $this->warn("No active news sources found.");
                return 0;
            }

            foreach ($sources as $source) {
                /** @var NewsSource $source */
                $this->info("Syncing source: {$source->name}");
                $log = $fetcherService->syncSource($source, $force);
                $this->displayLog($log);
            }
        }

        $this->info("Sync process completed.");
        return 0;
    }

    private function displayLog($log)
    {
        if (!$log) return;
        
        $this->table(
            ['Source', 'Status', 'Fetched', 'New', 'Skipped', 'Failed', 'Error'],
            [[
                $log->source->name ?? '-',
                $log->status,
                $log->articles_fetched,
                $log->articles_new,
                $log->articles_skipped,
                $log->articles_failed,
                $log->error_message ?? '-'
            ]]
        );
    }
}
