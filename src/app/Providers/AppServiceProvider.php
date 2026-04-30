<?php

namespace App\Providers;

use App\Services\Customer\Cart\CartService;
use App\Services\Customer\Cart\CartServiceInterface;
use App\Services\Customer\Favorite\FavoriteService;
use App\Services\Customer\Favorite\FavoriteServiceInterface;
use App\Services\Customer\Item\CategoryService;
use App\Services\Customer\Item\CategoryServiceInterface;
use App\Services\Customer\Item\ItemQueryService;
use App\Services\Customer\Item\ItemQueryServiceInterface;
use App\Services\Customer\Item\RankingQueryService;
use App\Services\Customer\Item\RankingQueryServiceInterface;
use App\Services\Customer\MyOrders\MyOrderService;
use App\Services\Customer\MyOrders\MyOrderServiceInterface;
use App\Services\Customer\Order\CheckoutService;
use App\Services\Customer\Order\CheckoutServiceInterface;
use App\Services\Customer\Order\Notification\OrderNotificationService;
use App\Services\Customer\Order\Notification\OrderNotificationServiceInterface;
use App\Services\Customer\Order\OrderService;
use App\Services\Customer\Order\OrderServiceInterface;
use App\Services\Customer\Order\StripeService;
use App\Services\Customer\Order\StripeServiceInterface;
use App\Services\Customer\Order\StripeServiceMock;
use App\Services\Customer\Order\WebhookService;
use App\Services\Customer\Order\WebhookServiceInterface;
use App\Services\Customer\Order\WebhookServiceMock;
use App\Services\Customer\Review\ReviewService;
use App\Services\Customer\Review\ReviewServiceInterface;
use App\Services\Customer\Shipment\ShipmentService;
use App\Services\Customer\Shipment\ShipmentServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Stripe\StripeClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ItemQueryServiceInterface::class, ItemQueryService::class);
        $this->app->bind(CartServiceInterface::class, CartService::class);
        $this->app->bind(FavoriteServiceInterface::class, FavoriteService::class);
        $this->app->bind(CategoryServiceInterface::class, CategoryService::class);
        $this->app->bind(CheckoutServiceInterface::class, CheckoutService::class);

        $this->app->singleton(StripeClient::class, function () {
            return new StripeClient(config('services.stripe.secret'));
        });

        $this->app->bind(ImageManager::class, function () {
            return new ImageManager(new Driver());
        });

        if (app()->environment('testing') || empty(config('services.stripe.secret'))) {
            $this->app->bind(StripeServiceInterface::class, StripeServiceMock::class);
        } else {
            $this->app->bind(StripeServiceInterface::class, StripeService::class);
        }
        
        if (app()->environment('testing') || empty(config('services.webhook.secret'))) {
            $this->app->bind(WebhookServiceInterface::class, WebhookServiceMock::class);
        } else {
            $this->app->bind(WebhookServiceInterface::class, WebhookService::class);
        }

        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(ShipmentServiceInterface::class, ShipmentService::class);
        $this->app->bind(OrderNotificationServiceInterface::class, OrderNotificationService::class);
        $this->app->bind(MyOrderServiceInterface::class, MyOrderService::class);
        $this->app->bind(RankingQueryServiceInterface::class, RankingQueryService::class);
        $this->app->bind(ReviewServiceInterface::class, ReviewService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict(! app()->isProduction());
    }
}
