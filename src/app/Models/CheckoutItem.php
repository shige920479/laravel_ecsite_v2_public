<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckoutItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'checkout_request_id', 'cart_id', 'shop_id', 'item_id', 'item_name', 'quantity',
        'price_ex_tax', 'tax_rate', 'price_tax', 'price_in_tax',
        'subtotal_ex_tax', 'subtotal_tax', 'subtotal_in_tax'
    ];

    /** relation */
    public function checkoutRequest(): BelongsTo
    {
        return $this->belongsTo(CheckoutRequest::class);
    }
    /** relation */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

}
