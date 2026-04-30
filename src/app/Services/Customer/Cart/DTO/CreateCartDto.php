<?php
namespace App\Services\Customer\Cart\DTO;

use App\Models\User;

class CreateCartDto
{
    public function __construct(
        public User $user,
        public int $itemId,
        public int $quantity
    )
    {}

    public static function fromRequest(User $user, array $data): self
    {
        return new self(
            user: $user,
            itemId: $data['item_id'],
            quantity: $data['quantity'],
        );
    }
}