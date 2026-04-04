<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Illuminate\Support\Facades\DB;
use App\Services\AssetUrlService;

class CleanAssetUrls extends Command
{
    protected $signature = 'assets:clean-urls 
                            {--dry-run : Show what would be changed without making changes}
                            {--table= : Only process specific table}';

    protected $description = 'Clean legacy absolute URLs in database to portable relative paths';

    /**
     * Table/column mapping for all asset URL columns.
     */
    private array $targets = [
        'news' => ['image_url'],
        'training_modules' => ['cover_image_path'],
        'education_contents' => ['thumbnail_url', 'file_url'],
        'settings' => ['value'], // Only for image-type settings
        'user_profiles' => ['avatar_url'],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $onlyTable = $this->option('table');

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE — No changes will be made.');
        }

        $totalFixed = 0;

        foreach ($this->targets as $table => $columns) {
            if ($onlyTable && $table !== $onlyTable) {
                continue;
            }

            foreach ($columns as $column) {
                $fixed = $this->cleanColumn($table, $column, (bool)$dryRun);
                $totalFixed += $fixed;
            }
        }

        $this->newLine();
        if ($dryRun) {
            $this->info("📊 Total records that WOULD be fixed: {$totalFixed}");
        } else {
            $this->info("✅ Total records fixed: {$totalFixed}");
        }

        return SymfonyCommand::SUCCESS;
    }

    private function cleanColumn(string $table, string $column, bool $dryRun): int
    {
        $this->info("Processing {$table}.{$column}...");

        // Find records with localhost/absolute URLs
        $records = DB::table($table)
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where(function ($query) use ($column) {
                $query->where($column, 'like', 'http://localhost%')
                      ->orWhere($column, 'like', 'http://127.0.0.1%')
                      ->orWhere($column, 'like', 'http://10.0.2.2%')
                      ->orWhere($column, 'like', 'storage/%')
                      ->orWhere($column, 'like', '/storage/%');
            })
            ->get(['id', $column]);

        if ($records->isEmpty()) {
            $this->line("  ✓ No legacy URLs found.");
            return 0;
        }

        $fixed = 0;
        foreach ($records as $record) {
            $oldValue = $record->$column;
            $newValue = $this->normalizePath($oldValue);

            if ($oldValue !== $newValue) {
                if ($dryRun) {
                    $this->line("  [ID:{$record->id}] {$oldValue}");
                    $this->line("           → {$newValue}");
                } else {
                    DB::table($table)->where('id', $record->id)->update([
                        $column => $newValue,
                    ]);
                }
                $fixed++;
            }
        }

        $this->line("  → {$fixed} records " . ($dryRun ? 'would be' : '') . " fixed.");
        return $fixed;
    }

    private function normalizePath(string $value): string
    {
        // Handle legacy absolute local URLs
        if (preg_match('#^https?://(localhost|127\.0\.0\.1|10\.0\.2\.2)(:\d+)?/(.*)$#i', $value, $matches)) {
            $value = $matches[3]; // Extract path after host
        }

        // Remove leading slash
        $value = ltrim($value, '/');

        // Remove 'storage/' prefix (both for static and dynamic determination)
        // But keep 'assets/' prefix as-is
        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, 8); // Remove 'storage/'
        }

        return $value;
    }
}
