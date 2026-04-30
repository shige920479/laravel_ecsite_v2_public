<?php
namespace App\Services\Owner\Csv;

use Illuminate\Http\UploadedFile;

class StockCsvReader
{
    public function read(UploadedFile $file): iterable
    {
        $handle = fopen($file->getRealPath(), 'r');
        
        try {
            $rowNumber = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;

                yield $rowNumber => $row;
            }

        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }
}

