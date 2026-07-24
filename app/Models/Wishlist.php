<?php

namespace App\Models;

use Database\Factories\WishlistFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['collection_id'])]
class Wishlist extends Model
{
    /** @use HasFactory<WishlistFactory> */
    use HasFactory;

    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }
}
