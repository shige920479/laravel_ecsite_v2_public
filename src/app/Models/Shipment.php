<?php

namespace App\Models;

use App\Enums\ShippingStatus;
use App\Services\Customer\Order\DTO\StoreShipmentCommand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'shop_id', 'shipping_name', 'shipping_postcode', 'shipping_address',
        'shipping_phone', 'shipping_status', 'shipped_at', 'delivered_at',
    ];

    protected $casts = [
        'shipping_status' => ShippingStatus::class,
        'shipped_at' => 'datetime',
        'deliverd_at' => 'datetime',
    ];

    public function fillFromDto(StoreShipmentCommand $dto): void
    {
        $this->order_id = $dto->orderId;
        $this->shop_id = $dto->shopId;
        $this->shipping_name = $dto->shippingName;
        $this->shipping_postcode = $dto->shippingPostcode;
        $this->shipping_address = $dto->shippingAddress;
        $this->shipping_phone = $dto->shippingPhone;
        $this->shipping_status = $dto->shippingStatus;
    }

    /** relation */ 
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    /** relation */ 
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
    /** relation */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

}
