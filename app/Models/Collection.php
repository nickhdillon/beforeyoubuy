<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'description', 'is_public'])]
class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasOne<Wishlist, $this> */
    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

    /** @return HasMany<CollectionItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(CollectionItem::class)->chaperone();
    }

    /** @param Builder<Collection> $query */
    #[Scope]
    protected function search(Builder $query, string $search): Builder
    {
        $pattern = '%'.Str::squish($search).'%';

        return $query->where(function (Builder $query) use ($pattern): void {
            $query->whereLike('name', $pattern)
                ->orWhereLike('description', $pattern)
                ->orWhereHas('items', function (Builder $query) use ($pattern): void {
                    $query->whereLike('name', $pattern)
                        ->orWhereLike('notes', $pattern)
                        ->orWhereLike('url', $pattern)
                        ->orWhereHas('tags', fn (Builder $query): Builder => $query->whereLike('name', $pattern));
                })
                ->orWhereHas('wishlist.items', function (Builder $query) use ($pattern): void {
                    $query->whereLike('name', $pattern)
                        ->orWhereLike('notes', $pattern)
                        ->orWhereLike('url', $pattern)
                        ->orWhereHas('tags', fn (Builder $query): Builder => $query->whereLike('name', $pattern));
                });
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
    }

    /** @return Attribute<Carbon, never> */
    protected function lastUpdatedAt(): Attribute
    {
        return Attribute::get(function (): Carbon {
            $latestItemUpdatedAt = $this->relationLoaded('items')
                ? $this->items->max('updated_at')
                : $this->items()->latest('updated_at')->value('updated_at');

            return Carbon::parse($latestItemUpdatedAt ?? $this->updated_at)
                ->max($this->updated_at);
        })->withoutObjectCaching();
    }

    public static function booted(): void
    {
        static::creating(function (self $collection): void {
            $collection->slug = Str::slug($collection->name);
        });

        static::created(function (self $collection): void {
            $collection->wishlist()->create();
        });

        static::updating(function (self $collection): void {
            if ($collection->isDirty('name')) {
                $collection->slug = Str::slug($collection->name);
            }
        });
    }
}
