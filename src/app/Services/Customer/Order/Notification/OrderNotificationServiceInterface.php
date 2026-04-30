<?php
namespace App\Services\Customer\Order\Notification;

use App\Services\Customer\Order\DTO\FailedOrderDto;
use App\Services\Customer\Order\DTO\OrderProcessResultDto;

interface OrderNotificationServiceInterface
{
    public function notifyCustomer(OrderProcessResultDto $result): void;
    public function notifyOwners(OrderProcessResultDto $result): void;
    public function notifyCheckoutExpired(FailedOrderDto $result): void;
    public function notifyPaymentFailed(FailedOrderDto $result): void;
  
}