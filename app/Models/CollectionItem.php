<?php

namespace App\Models;

use Database\Factories\CollectionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

#[Fillable(['collection_id', 'image_path', 'name', 'url', 'quantity', 'notes', 'rating'])]
class CollectionItem extends Model
{
    /** @use HasFactory<CollectionItemFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::deleting(function (CollectionItem $item): void {
            $item->tags()->detach();
        });
    }

    protected $attributes = [
        'quantity' => 1,
    ];

    /** @return BelongsTo<Collection, $this> */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /** @return MorphToMany<Tag, $this> */
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable')->orderBy('name');
    }

    /** @param Builder<CollectionItem> $query */
    #[Scope]
    protected function search(Builder $query, string $search): Builder
    {
        $pattern = '%'.Str::squish($search).'%';

        return $query->where(function (Builder $query) use ($pattern): void {
            $query->whereLike('name', $pattern)
                ->orWhereLike('notes', $pattern)
                ->orWhereLike('url', $pattern)
                ->orWhereHas('tags', fn (Builder $query): Builder => $query->whereLike('name', $pattern));
        });
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
