<?php
namespace App\Services\Customer\Order;

use Illuminate\Support\Collection;

interface StripeServiceInterface
{
    public function createStripeSession(Collection $checkoutItems): string;
}