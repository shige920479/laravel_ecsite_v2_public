<?php
namespace App\Services\Customer\Item;

use Illuminate\Support\Collection;

interface CategoryServiceInterface
{
    public function getTree(): Collection;
    public function clearCache(): void;
}