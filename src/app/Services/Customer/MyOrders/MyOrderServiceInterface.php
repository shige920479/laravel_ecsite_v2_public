<?php
namespace App\Services\Customer\MyOrders;

use App\Services\Customer\MyOrders\DTO\MyOrderQuery;
use Illuminate\Pagination\LengthAwarePaginator;

interface MyOrderServiceInterface
{
    public function getMyOrders(MyOrderQuery $myOrderQuery): LengthAwarePaginator;
}