<?php
namespace App\Services\Owner\Csv;

interface CsvDefinition
{
    public function headers(): array;
    public function mapRows($row): array;
    public function filename(): string;
}