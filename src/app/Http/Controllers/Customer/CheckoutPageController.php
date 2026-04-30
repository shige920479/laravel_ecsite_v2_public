<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderConfirmRequest;
use App\Http\Resources\Customer\Order\CheckoutConfirmResource;
use App\Models\Cart;
use App\Models\Order;
use App\Services\Customer\Order\CheckoutServiceInterface;
use Illuminate\Http\Request;

class CheckoutPageController extends Controller
{
    public function __construct(private CheckoutServiceInterface $checkoutService)
    {
    }

    public function confirm(OrderConfirmRequest $request)
    {
        $carts = Cart::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('id', $request->validated('ids'))
            ->with(['item'])
            ->get();

        $errors = $this->checkoutService->isOrderable($request->user(), $carts);
        if (! empty($errors)) {
            return back()->withErrors($errors);
        }
        
        return view('user.checkout.confirm', ['carts' => CheckoutConfirmResource::collection($carts)]);
    }

    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
 
        $order = Order::with('orderItems.item.mainImage')
            ->where('stripe_session_id', $sessionId)
            ->first();
        
        if (! $order) {
            sleep(1);
            $order = Order::with('orderItems.item.mainImage')
                ->where('stripe_session_id', $sessionId)
                ->first();
        }

        return view('user.checkout.success', ['order' => $order]);
    }

    public function cancel()
    {
        return view('user.checkout.cancel');
    }

}
