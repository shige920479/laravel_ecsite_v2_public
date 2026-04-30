<?php

namespace App\Console\Commands;

use App\Services\Owner\CleanUpTmpService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CleanTmpImage extends Command
{
    protected $signature = 'app:clean-tmp-image';
    protected $description = 'Command description';

    public function __construct(private CleanUpTmpService $cleanUpTmpService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $expiration = 12;
        $result = $this->cleanUpTmpService->cleanTmpFile($expiration);

        $status = $result ? '✅ SUCCESS' : '⚠️ PARTIAL';

        $message = implode("\n", [
            "*Laravel ECサイトV2 Tmp Cleanup Report*",
            "Status: {$status}"
        ]);

        $exitCode = Command::SUCCESS;

        try {
            Http::retry(3, 200)
                ->timeout(5)
                ->asJson()
                ->post(config('services.slack.webhook_url'), [
                    'text' => $message
                ]);

        } catch (\Throwable $e) {
            Log::warning('Slack notification failed', [
                'error' => $e->getMessage()
            ]);
        }

        return $exitCode;
    }
}
