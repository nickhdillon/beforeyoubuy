<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class FilterItemQuery
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function apply(
        Builder $query,
        string $type,
        int|float|string $value,
    ): Builder {
        return match ($type) {
            'tag' => $query->whereHas('tags', fn (Builder $query): Builder => $query->whereKey((int) $value)),
            'minimum_rating' => $query->where('rating', '>=', (float) $value),
            'quantity' => $value === 'single' ? $query->where('quantity', 1) : $query->where('quantity', '>', 1),
            'link' => $value === 'with' ? $query->whereNotNull('url')->where('url', '!=', '') : self::whereBlank($query, 'url'),
            'photo' => $value === 'with' ? $query->whereNotNull('image_path')->where('image_path', '!=', '') : self::whereBlank($query, 'image_path'),
            'sort_name' => $query->orderByRaw('name is null, name'),
            'sort_rating' => $query->orderByDesc('rating'),
            'sort_quantity' => $query->orderByDesc('quantity'),
            'sort_oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private static function whereBlank(Builder $query, string $column): Builder
    {
        return $query->where(fn (Builder $query): Builder => $query->whereNull($column)->orWhere($column, ''));
    }
}
