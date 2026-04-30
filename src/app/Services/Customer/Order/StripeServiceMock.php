<?php
namespace App\Services\Customer\Order;

use Exception;
use Illuminate\Support\Collection;

class StripeServiceMock implements StripeServiceInterface
{
    public function createStripeSession(Collection $checkoutItems): string
    {
        return "test-url";
    }
}