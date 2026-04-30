<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin IdeHelperStockHistory
 */
class StockHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id', 'stock_diff', 'stock_after', 'reason'
    ];

    /** relation */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /** 商品で絞る */
    public function scopeForItem($query, Item $item)
    {
        return $query->where('item_id', $item->id);
    }

    /** 開始日 */
    public function scopeFromDate($query, string $date)
    {
        return $query->whereDate('created_at', '>=', $date);
    }
    
    /** 終了日 */
    public function scopeToDate($query, string $date)
    {
        return $query->whereDate('created_at', '<=', $date);
    }

    /** 入庫 */
    public function scopeOnlyIn($query)
    {
        return $query->where('stock_diff', '>', 0);
    }

    /** 出庫 */
    public function scopeOnlyOut($query)
    {
        return $query->where('stock_diff', '<', 0);
    }

 
}
