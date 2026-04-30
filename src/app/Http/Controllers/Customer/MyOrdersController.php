<?php

namespace App\Http\Controllers\Customer;

use App\Helper\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyOrdersIndexRequest;
use App\Models\Order;
use App\Services\Customer\MyOrders\DTO\MyOrderQuery;
use App\Services\Customer\MyOrders\MyOrderServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MyOrdersController extends Controller
{
    public function __construct(private MyOrderServiceInterface $myOrderService)
    {
    }

    public function index(MyOrdersIndexRequest $request)
    {
        $user = $request->user();
        $myOrderQuery = MyOrderQuery::fromRequest($user->id, $request->validated());
        $orders = $this->myOrderService->getMyOrders($myOrderQuery);

        return view('user.mypage.orders', ['orders' => $orders]);
    }

    public function show(Order $order)
    {
        Gate::authorize('view', $order);
        $order->load('shipments.orderItems');

        return view('user.mypage.order-show', ['order' => $order]);

    }
}
