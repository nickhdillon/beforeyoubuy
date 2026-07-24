<?php

namespace App\Models;

use Database\Factories\CollectionItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['collection_id', 'image_path', 'name', 'quantity', 'notes', 'rating'])]
class CollectionItem extends Model
{
    /** @use HasFactory<CollectionItemFactory> */
    use HasFactory;

    protected $attributes = [
        'quantity' => 1,
    ];

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
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
