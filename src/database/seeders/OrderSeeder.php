<?php

namespace Database\Seeders;

use App\Enums\ShippingStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shipment;
use App\Models\User;
use App\Services\TaxCalculator;
use App\Support\AppLog;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $items = Item::with('shop')->get();
        $popularItems = $items->random(5);
        
        foreach ($users as $user) {

            DB::transaction(function () use ($user, $items, $popularItems) {

                try {
                    $purchaseNum = rand(5, 20);

                    for ($i = 0; $i < $purchaseNum; $i++) {
                        $orderedAt = $this->randomDate();

                        $order = Order::factory()->for($user)->create([
                            'ordered_at' => $orderedAt,
                        ]);

                        $selectedItems = $this->setPopularItem($items, $popularItems);
                        $grouped = $selectedItems->groupBy('shop_id');

                        $orderItems =collect();

                        $shippingStatus = $this->getShippingStatus($orderedAt);
                        $shippedAt = null;
                        if ($shippingStatus === ShippingStatus::SHIPPED || $shippingStatus === ShippingStatus::DELIVERED) {
                            $diffDays = now()->diffInDays($orderedAt);
                            $shippedAt = $orderedAt->addDays(rand(1, $diffDays));
                        }
                        foreach ($grouped as $shopId => $groupItems) {
                            $shipment = Shipment::factory()->for($order)->forUser($user)->create([
                                'shop_id' => $shopId,
                                'shipping_status' => $shippingStatus,
                                'shipped_at' => $shippedAt,
                            ]);

                            foreach ($groupItems as $item) {
                                $quantity = rand(1, 3);
                                $priceData = TaxCalculator::calculateItem($item->price_ex_tax, $quantity);

                                $orderItem = OrderItem::factory()->for($shipment)->for($item)->create([
                                    'item_name' => $item->name,
                                    'quantity' => $quantity,
                                    'price_ex_tax' => $item->price_ex_tax,
                                    'tax_rate' => $priceData['tax_rate'],
                                    'price_tax' => $priceData['unit_tax_amount'],
                                    'price_in_tax' => $priceData['unit_in_tax'],
                                    'subtotal_ex_tax' => $priceData['subtotal_ex_tax'],
                                    'subtotal_tax' => $priceData['subtotal_tax'],
                                    'subtotal_in_tax' => $priceData['subtotal_in_tax'],
                                    'created_at' => $orderedAt,
                                    'updated_at' => $orderedAt,
                                ]);

                                $orderItems->push($orderItem);
                            }
                        }
                        
                        $order->update([
                            'order_number' => 'ORD-' . $orderedAt->format('Ymd') . '-' . Str::upper(Str::random(6)),
                            'total_ex_tax' => $orderItems->sum('subtotal_ex_tax'),
                            'total_tax' =>  $orderItems->sum('subtotal_tax'),
                            'total_in_tax' =>  $orderItems->sum('subtotal_in_tax'),
                        ]);
                    }
                } catch (\Exception $e) {
                    AppLog::error('OrderSeeder失敗', $e);
                    throw $e;
                }

            });
        }
    }

    private function randomDate(): Carbon
    {
        $rand = rand(1, 100);
        if ($rand <= 60) {
            return Carbon::instance(fake()->dateTimeBetween('-1 month', 'now'));
        } elseif ($rand <= 85) {
            return Carbon::instance(fake()->dateTimeBetween('-2 months', '-1 month'));
        } else {
            return Carbon::instance(fake()->dateTimeBetween('-3 months', '-2 months'));
        }
    }

    private function setPopularItem(Collection $items, Collection $popularItems): Collection
    {
        $count = rand(1, 3);
        $selectedItems = collect();

        for ($i = 0; $i < $count; $i++) {
            if (rand(1, 100) <= 40) {
                $item = $popularItems->random();
            } else {
                $item = $items->random();
            }

            if ($selectedItems->contains('id', $item->id)) {
                $i--;
                continue;
            }
            $selectedItems->push($item);
        }

        return $selectedItems;
    }

    private function getShippingStatus(Carbon $orderedAt): ShippingStatus
    {
        if ($orderedAt < now()->subDays(7)) {
            return ShippingStatus::DELIVERED;
        }

        if ($orderedAt < now()->subDays(2)) {
            return ShippingStatus::SHIPPED;
        }

        return fake()->randomElement([
            ShippingStatus::UNSHIPPED,
            ShippingStatus::PREPARING,
        ]);
    }
}
