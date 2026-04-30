<?php
namespace App\Services\Owner\Csv;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvExportService
{
    /** CSVファイルのダウンロード処理 */
    public function download(Builder $query, CsvDefinition $definition): StreamedResponse
    {
        return response()->streamDownload(function () use ($query, $definition) {

            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $definition->headers());

            $query->chunk(100, function ($rows) use ($handle, $definition) {
                foreach($rows as $row) {
                    fputcsv($handle, $definition->mapRows($row));
                }
            });

            fclose($handle);
        }, $definition->filename(), [
            'Content-type' => 'text/csv; charset=UTF-8'
        ]);
    }
}