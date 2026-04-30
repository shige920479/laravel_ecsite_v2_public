<?php
namespace App\Services\Customer\MyOrders\DTO;

use App\Helper\Helper;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;

class MyOrderQuery
{
    public function __construct(
        public readonly int $userId,
        public readonly array $keywords,
        public readonly ?string $from,
        public readonly ?string $to,
        public readonly ?string $status,
    )
    {
    }

    public static function fromRequest(int $userId, array $data): self
    {
        return new self(
            userId: $userId,
            keywords: Helper::trimSearchWord($data['keyword'] ?? ''),
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            status: $data['status'] ?? null,
        );
    }

    public function apply(Builder $query): Builder
    {
        return 
            $query
            ->when(! empty($this->keywords), fn ($query) =>
                $query->whereHas('shipments.orderItems', function ($query) {
                    foreach ($this->keywords as $word) {
                        $query->whereLike('item_name', "%{$word}%");
                    }
                })
            )
            ->when($this->from, fn ($query) => 
                $query->whereDate('ordered_at', '>=', $this->from)
            )
            ->when($this->to, fn ($query) => 
                $query->whereDate('ordered_at', '<=', $this->to)
            )
            ->when($this->status, fn ($query) =>
                $query->whereHas('shipments', fn ($query) =>
                    $query->where('shipping_status', $this->status)
                )
            );
    }
}