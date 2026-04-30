<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ResetDatabase extends Command
{
    protected $signature = 'app:reset-database';
    protected $description = 'Migrate fresh and seed Daily';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            Artisan::call('migrate:fresh --seed --force');

            Http::retry(3, 200)
                ->timeout(5)
                ->asJson()
                ->post(config('services.slack.webhook_url'), [
                    'text' => "*Laravel ECサイトV2 Reset Report*\n" . "Status: ✅ SUCCESS\n"
                ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            Log::error('Reset DB error: ' . $e->getMessage(), [
                'exception' => $e
            ]);

            Http::retry(3, 200)
                ->timeout(5)
                ->asJson()
                ->post(config('services.slack.webhook_url'), [
                    'text' => 
                        "*Laravel ECサイトV2 Reset Report*\n" .
                        "Status: ⚠️ WARNINGS / ERRORS\n" .
                        "```{$e->getMessage()}```"
                ]);
            
            return Command::FAILURE;
        }
    }
}
