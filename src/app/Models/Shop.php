<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperShop
 */
class Shop extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id', 'name', 'information', 'filename', 'is_selling'
    ];

    protected $casts = [
        'is_selling' => 'boolean',
    ];

    /** ショップ画像を安全に取得しパスを返す */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->filename 
                    ? Storage::url($this->filename)
                    : asset('images/noimage.png');
        });
    }

    /** relation */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Owner::class);
    }
    /** relation */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }
    /** relation */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
