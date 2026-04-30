<?php

namespace App\Models;

use App\Enums\CheckoutStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CheckoutRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'status', 'total_ex_tax', 'total_tax', 'total_in_tax', 'expires_at'
    ];

    protected $casts = [
        'status' => CheckoutStatus::class,
        'expires_at' => 'datetime',
    ];

    /** relation */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /** relation */
    public function checkoutItems(): HasMany
    {
        return $this->hasMany(CheckoutItem::class);
    }
}
