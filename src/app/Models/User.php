<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Helper\Helper;
use App\Services\Customer\Order\Exceptions\NotRegiteredAccountException;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @mixin IdeHelperUser
 */
// class User extends Authenticatable implements MustVerifyEmail // デプロイ時はメール認証OFF
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'nickname', 'email', 'google_id', 'email_verified_at', 'password',  'postcode', 'address', 'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isRegistered(): bool
    {
        return filled($this->postcode)
            && filled($this->address)
            && filled($this->phone);
    }

    public function ensureRegistered(): void
    {
        if (! $this->postcode || ! $this->address || ! $this->phone) {
            throw new NotRegiteredAccountException();
        }
    }

    /** 検索ワードで名前を検索(複数可) */
    public function scopeSearchByName(Builder $query, string $searchWord): Builder
    {
        $wordList = Helper::trimSearchWord($searchWord);

        $query->where(function ($query) use ($wordList) {
            foreach ($wordList as $word) {
                $query->orWhereLike('name', "%{$word}%");
            }
        });

        return $query;
    } 

    /** relation */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
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
    /** relation */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
    /** relation 中間テーブル */
    public function favoriteItems(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'favorites')->withTimestamps();
    }
    /** relation */
    public function checkoutRequests(): HasMany
    {
        return $this->hasMany(CheckoutRequest::class);
    }
    /** relation 中間テーブル  */
    public function helpfulReviews(): BelongsToMany
    {
        return $this->belongsToMany(Review::class, 'review_helpfuls')->withTimestamps();
    }


}
