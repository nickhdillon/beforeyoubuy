<?php

namespace App\Models;

use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable(['user_id', 'name'])]
class Tag extends Model
{
    /** @use HasFactory<TagFactory> */
    use HasFactory;

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return MorphToMany<CollectionItem, $this> */
    public function collectionItems(): MorphToMany
    {
        return $this->morphedByMany(CollectionItem::class, 'taggable');
    }

    /** @return MorphToMany<WishlistItem, $this> */
    public function wishlistItems(): MorphToMany
    {
        return $this->morphedByMany(WishlistItem::class, 'taggable');
    }
}
