<?php
namespace App\Services\Owner;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanUpTmpService
{
    public function cleanTmpFile(int $expiration): bool
    {
        $files = Storage::allFiles('tmp');

        return $this->deleteFiles($files, $expiration);
    }

    public function deleteFiles(array $files, int $expiration): bool
    {
        $deleteTargets = [];

        $threshold = now()->subHours($expiration)->timestamp;

        foreach($files as $file) {
            $lastModified = Storage::lastModified($file);

            if ($lastModified > $threshold) {
                continue;
            }

            $deleteTargets[] = $file;
        }

        if (! empty($deleteTargets)) {
            if (! Storage::delete($deleteTargets)) {
                Log::warning('一部の画像削除に失敗した可能性有り');
                
                return false;
            }
        }

        return true;
    }

}