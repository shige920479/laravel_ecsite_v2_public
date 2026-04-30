<?php

namespace App\Models;

use App\Services\Customer\Order\DTO\StoreOrderItemCommand;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id', 'item_id', 'item_name','quantity', 'price_ex_tax', 'tax_rate',
        'price_tax', 'price_in_tax', 'subtotal_ex_tax', 'subtotal_tax', 'subtotal_in_tax'
    ];

    /** DTOからインスタン生成 */
    public function fillFromDto(StoreOrderItemCommand $dto): void
    {
        $this->shipment_id = $dto->shipmentId;
        $this->item_id = $dto->itemId;
        $this->item_name = $dto->itemName;
        $this->quantity = $dto->quantity;
        $this->price_ex_tax = $dto->priceExTax;
        $this->tax_rate = $dto->taxRate;
        $this->price_tax = $dto->priceTax;
        $this->price_in_tax = $dto->priceInTax;
        $this->subtotal_ex_tax = $dto->subtotalExTax;
        $this->subtotal_tax = $dto->subtotalTax;
        $this->subtotal_in_tax = $dto->subtotalInTax;
    }

    /** relation */
    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
    /** relation */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
