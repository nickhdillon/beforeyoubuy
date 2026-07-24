<?php

namespace App\Policies;

use App\Models\CollectionItem;
use App\Models\User;

class CollectionItemPolicy
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
    public function view(?User $user, CollectionItem $collectionItem): bool
    {
        return $collectionItem->collection->is_public || $user?->is($collectionItem->collection->user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CollectionItem $collectionItem): bool
    {
        return $user->is($collectionItem->collection->user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CollectionItem $collectionItem): bool
    {
        return $user->is($collectionItem->collection->user);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CollectionItem $collectionItem): bool
    {
        return $user->is($collectionItem->collection->user);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CollectionItem $collectionItem): bool
    {
        return $user->is($collectionItem->collection->user);
    }
}
