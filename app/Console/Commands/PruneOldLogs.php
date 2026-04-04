<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PruneOldLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:prune {--days=365 : The number of days to retain logs}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Prune old audit logs and telemetry data to maintain system performance.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');
        $cutoff = Carbon::now()->subDays($days);

        $this->info("Pruning logs older than {$days} days (Cutoff: {$cutoff->toDateString()})...");

        $tables = [
            'audit_logs',
            'vr_session_events',
            'vr_ai_interactions',
            'ai_usage_logs',
        ];

        foreach ($tables as $table) {
            if (DB::getSchemaBuilder()->hasTable($table)) {
                $count = DB::table($table)
                    ->where('created_at', '<', $cutoff)
                    ->delete();
                
                $this->line("- Removed {$count} records from `{$table}`.");
            }
        }

        $this->info('Log pruning completed successfully.');
    }
}
