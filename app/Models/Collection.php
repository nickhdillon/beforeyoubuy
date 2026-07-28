<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
            $collection->slug = self::uniqueSlug($collection->name);
        });

        static::created(function (self $collection): void {
            $collection->wishlist()->create();
        });

        static::updating(function (self $collection): void {
            if ($collection->isDirty('name')) {
                $collection->slug = self::uniqueSlug($collection->name, $collection->getKey());
            }
        });
    }

    private static function uniqueSlug(string $name, int|string|null $exceptId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (self::query()
            ->when($exceptId !== null, fn ($query) => $query->whereKeyNot($exceptId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
