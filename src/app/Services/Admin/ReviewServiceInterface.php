<?php
namespace App\Services\Admin;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReviewServiceInterface
{
    public function getReviews(array $param): LengthAwarePaginator;
}