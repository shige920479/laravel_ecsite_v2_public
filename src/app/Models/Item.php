<?php

namespace App\Models;

use App\Exceptions\OverStockException;
use App\Exceptions\SalesSuspendedException;
use App\Helper\Helper;
use App\Services\TaxCalculator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin IdeHelperItem
 */
class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'shop_id', 'item_category_id', 'name', 'information', 'price_ex_tax', 'stock_current', 'is_selling',
    ];

    protected $casts = [
        'is_selling' => 'boolean',
    ];

    /** 税込金額でformat */
    protected function priceTaxIn(): Attribute
    {
        return Attribute::make(
            get: function () {
                $results = TaxCalculator::calculateItem($this->price_ex_tax, 1);
                return $results['unit_in_tax'];
            }
        );
    }
    /** 最新日付をフォーマット(Y/m/d) */
    protected function createdAtFormatted(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->created_at?->format('Y/m/d');
            }
        );
    }

    /** メイン画像のファイルパスを安全に取得 */
    protected function mainImageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->mainImage?->filename
                    ? Storage::url($this->mainImage->filename)
                    : asset('images/noimage.png');
            }
        );
    }
    
    /** 全商品画像のファイルパスを安全に取得（商品詳細ページ専用）*/
    protected function imageUrls(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->itemImages->isNotEmpty()
                    ? $this->itemImages->map(fn ($image) => [
                        'id' => $image->id,
                        'filename' => Storage::url($image->filename),
                    ])
                    : collect([[
                        'id' => 1,
                        'filename' => asset('images/noimage.png'),
                    ]]);
            }
        );
    }

    /** 在庫が足りるか判定メソッド */
    public function hasEnoughStock(int $quantity): bool
    {
        return $this->stock_current >= $quantity;
    }

    /** 購入可能か判定（販売中＆在庫） */
    public function isPurchasable(int $quantity): void
    {
        if (! $this->is_selling) {
            throw new SalesSuspendedException();
        }
        if (! $this->hasEnoughStock($quantity)) {
            throw new OverStockException();
        }
    }

    /** 商品名検索(オーナー側の商品一覧) */
    public function scopeSearchItemName(Builder $query, string $searchWord): Builder
    {
        $words = Helper::trimSearchWord($searchWord);

        return $query->where(function ($q) use ($words) {
            foreach ($words as $word) {
                $q->whereLike('name', "%{$word}%");
            }
        });
    }
    /** オーナー画面の商品一覧ソート */
    public function scopeSortBy(Builder $query, string $sort): Builder
    {
        [$column, $direction] = config("constants.owner_item_sort.options.{$sort}");

        return $query->orderBy($column, $direction);
    }
    /** ユーザー画面のキーワード検索用 */
    public function scopeSearchByNameOrShop(Builder $query, string $word): Builder
    {
        $trimWords = Helper::trimSearchWord($word);
        
        return $query->where(function ($query) use ($trimWords) {
            foreach($trimWords as $word) {
                $query->where(function ($query) use ($word) {
                    $query->whereLike('name', "%{$word}%")
                        ->orWhereHas('shop', fn ($q) => $q->whereLike('name', "%{$word}%"));
                });
            }
        });
    }
    /** カテゴリーで絞り込み */
    public function scopeFilterCategorySlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('itemCategory.subCategory.category', fn ($q) =>
            $q->where('slug', $slug)
        );
    }
    /** サブカテゴリーで絞り込み */
    public function scopeFilterSubCategorySlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('itemCategory.subCategory', fn ($q) =>
            $q->where('slug', $slug)
        );
    }
    /** アイテムカテゴリーで絞り込み  */
    public function scopeFilterItemCategorySlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('itemCategory', fn ($q) => $q->where('slug', $slug));
    }
    /** ユーザー画面の並べ替え */
    public function scopeSortItemList(Builder $query, string $sort): Builder
    {
        [$column, $direction] = config("constants.item_sort.options.{$sort}");

        return $query->orderBy($column, $direction);
    }

    /** 評価（star）の平均 */
    public function scopeWithAvgStar(Builder $query): Builder
    {
        return $query->withAvg('reviews as avg_star', 'star');
    }
    /** レビューの数 */
    public function scopeWithReviewsCount(Builder $query): Builder
    {
        return $query->withCount('reviews as reviews_count');
    }

    /** 販売数量の合計値(期間指定) */
    public function scopeWithSales(Builder $query, int $period): Builder
    {
        return $query->withSum([
            'orderItems as sales' => fn ($q) =>
                $q->whereDate('created_at', '>=', now()->subDays($period))
        ], 'quantity');
    }
    /** 閲覧数の合計値(期間指定) */
    public function scopeWithViewCounts(Builder $query, int $period): Builder
    {
        return $query->withCount([
            'itemViews as view_counts' => fn ($q) =>
                $q->whereDate('created_at', '>=', now()->subDays($period))
        ]);
    }

    /** relation */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }
    /** relation */
    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class);
    }
    /** relation */
    public function itemImages(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('sort_order', 'asc');
    }
    /** relation */
    public function mainImage(): HasOne
    {
        return $this->hasOne(ItemImage::class)->orderBy('sort_order', 'asc');
    }
    /** relation */
    public function stockHistories(): HasMany
    {
        return $this->hasMany(StockHistory::class);
    }
    /** relation */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
    /** relation */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
    /** relation */
    public function itemViews(): HasMany
    {
        return $this->hasMany(ItemView::class);
    }
    /** relation 中間テーブル */
    public function favoritedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
    /** relation */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
    /** relation */
    public function checkoutItems(): HasMany
    {
        return $this->hasMany(CheckoutItem::class);
    }
}
