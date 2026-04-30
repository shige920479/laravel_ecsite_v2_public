<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Services\Customer\Order\DTO\StoreOrderCommand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_number', 'total_ex_tax', 'total_tax', 'total_in_tax', 'payment_method',
        'stripe_session_id', 'payment_status', 'ordered_at'
    ];

    protected $casts = [
        'payment_method' => PaymentMethod::class,
        'payment_status' => OrderStatus::class,
        'ordered_at' => 'datetime',
    ];

    /** DTOをインスタンス化 */
    public function fillFromDto(StoreOrderCommand $dto): void
    {
        $this->user_id = $dto->userId;
        $this->total_ex_tax = $dto->totalExTax;
        $this->total_tax = $dto->totalTax;
        $this->total_in_tax = $dto->totalInTax;
        $this->payment_method = $dto->paymentMethod;
        $this->stripe_session_id = $dto->stripeSessionId;
        $this->payment_status = $dto->paymentStatus;
        $this->ordered_at = $dto->orderedAt;
    }

    /** relation */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /** relation */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
    /** relation */
    public function orderItems(): HasManyThrough
    {
        return $this->hasManyThrough(OrderItem::class, Shipment::class);
    }
    /** relation */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
