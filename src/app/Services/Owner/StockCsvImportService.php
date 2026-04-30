<?php
namespace App\Services\Owner;

use App\Services\Owner\Csv\StockCsvApplyService;
use App\Services\Owner\Csv\StockCsvReader;
use App\Services\Owner\Csv\StockCsvValidator;
use Illuminate\Http\UploadedFile;

class StockCsvImportService
{
    public function __construct(
        private StockCsvReader $reader,
        private StockCsvValidator $validator,
        private StockCsvApplyService $applyService,
    ){}

    public function import(UploadedFile $file, int $ownerId): int
    {
        $rows = $this->reader->read($file);
        $rowsForProcessing = $this->validator->validate($rows);
        $count = $this->applyService->apply($rowsForProcessing, $ownerId);
        
        return $count;
    }
}
