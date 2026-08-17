<?php

namespace App\Models;

use Database\Factories\WishlistItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable(['wishlist_id', 'image_path', 'name', 'url', 'notes', 'quantity', 'rating'])]
class WishlistItem extends Model
{
    /** @use HasFactory<WishlistItemFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (WishlistItem $item): void {
            $item->tags()->detach();
        });
    }

    protected $attributes = [
        'quantity' => 1,
    ];

    /** @return BelongsTo<Wishlist, $this> */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->orderBy('name');
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'rating' => 'float',
        ];
    }
}
