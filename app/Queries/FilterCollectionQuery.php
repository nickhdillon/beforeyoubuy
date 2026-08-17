<?php

namespace App\Queries;

use App\Models\Collection;
use Illuminate\Database\Eloquent\Builder;

final class FilterCollectionQuery
{
    /** @param Builder<Collection> $query */
    public static function apply(
        Builder $query,
        string $type,
        int|float|string $value,
    ): Builder {
        return match ($type) {
            'visibility' => $query->where('is_public', $value === 'public'),
            'tag', 'minimum_rating', 'quantity' => self::whereItemMatches($query, $type, $value),
            'contents' => match ($value) {
                'collection' => $query->has('items'),
                'wishlist' => $query->has('wishlist.items'),
                default => $query->doesntHave('items')->doesntHave('wishlist.items'),
            },
            'sort_name' => $query->orderBy('name'),
            'sort_items' => $query->orderByDesc('items_count'),
            'sort_oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }

    /**
     * @param  Builder<Collection>  $query
     * @return Builder<Collection>
     */
    private static function whereItemMatches(Builder $query, string $type, int|float|string $value): Builder
    {
        return $query->where(fn (Builder $query): Builder => $query
            ->whereHas('items', fn (Builder $query): Builder => self::constrainItem($query, $type, $value))
            ->orWhereHas('wishlist.items', fn (Builder $query): Builder => self::constrainItem($query, $type, $value)));
    }

    private static function constrainItem(Builder $query, string $type, int|float|string $value): Builder
    {
        return match ($type) {
            'tag' => $query->whereHas('tags', fn (Builder $query): Builder => $query->whereKey((int) $value)),
            'minimum_rating' => $query->where('rating', '>=', (float) $value),
            default => $value === 'single' ? $query->where('quantity', 1) : $query->where('quantity', '>', 1),
        };
    }
}
