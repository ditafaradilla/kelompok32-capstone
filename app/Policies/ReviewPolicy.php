<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    /**
     * Pelanggan hanya boleh membuat ulasan jika memiliki pesanan berstatus 'selesai' berisi produk tersebut (FRI-07).
     */
    public function create(User $user, Product $product): bool
    {
        return $product->canBeReviewedBy($user);
    }

    /**
     * Ulasan hanya boleh diubah oleh pemilik ulasan.
     */
    public function update(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }

    /**
     * Ulasan hanya boleh dihapus oleh pemilik ulasan.
     */
    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id;
    }
}

