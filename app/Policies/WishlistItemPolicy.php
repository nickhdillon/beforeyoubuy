<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Auth\Access\Response;

class WishlistItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, WishlistItem $wishlistItem): Response
    {
        return $user?->is($wishlistItem->wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Wishlist $wishlist): Response
    {
        return $user->is($wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, WishlistItem $wishlistItem): Response
    {
        return $user->is($wishlistItem->wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, WishlistItem $wishlistItem): Response
    {
        return $user->is($wishlistItem->wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, WishlistItem $wishlistItem): Response
    {
        return $user->is($wishlistItem->wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, WishlistItem $wishlistItem): Response
    {
        return $user->is($wishlistItem->wishlist->collection->user)
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
