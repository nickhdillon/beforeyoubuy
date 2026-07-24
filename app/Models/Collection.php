<?php

namespace App\Models;

use Database\Factories\CollectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'name', 'description', 'is_public'])]
class Collection extends Model
{
    /** @use HasFactory<CollectionFactory> */
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function wishlist(): HasOne
    {
        return $this->hasOne(Wishlist::class);
    }

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

    public static function booted(): void
    {
        static::creating(function (self $collection): void {
            $collection->slug = Str::slug($collection->name);
        });

        static::updating(function (self $collection): void {
            if ($collection->isDirty('name')) {
                $collection->slug = Str::slug($collection->name);
            }
        });
    }
}
