<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoveCollectionItem
{
    public function toWishlist(CollectionItem $item, Wishlist $wishlist): WishlistItem
    {
        $destinationImagePath = $this->moveImage($item->image_path, 'wishlist-items');

        try {
            return DB::transaction(function () use ($destinationImagePath, $item, $wishlist): WishlistItem {
                $tagIds = $item->tags()->pluck('tags.id');
                $wishlistItem = $wishlist->items()->create([
                    'image_path' => $destinationImagePath,
                    ...$this->itemAttributes($item),
                ]);
                $wishlistItem->tags()->sync($tagIds);

                $item->delete();

                return $wishlistItem;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->move($destinationImagePath, $item->image_path);

            throw $exception;
        }
    }

    public function toCollection(WishlistItem $item, Collection $collection): CollectionItem
    {
        $sourceImagePath = $item->image_path;

        if (! is_string($sourceImagePath)) {
            throw new \RuntimeException('The wishlist item needs a photo before it can be added to the collection.');
        }

        $destinationImagePath = $this->moveImage($sourceImagePath, 'collection-items');

        try {
            return DB::transaction(function () use ($collection, $destinationImagePath, $item): CollectionItem {
                $tagIds = $item->tags()->pluck('tags.id');
                $collectionItem = $collection->items()->create([
                    'image_path' => $destinationImagePath,
                    ...$this->itemAttributes($item),
                ]);
                $collectionItem->tags()->sync($tagIds);

                $item->delete();

                return $collectionItem;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->move($destinationImagePath, $sourceImagePath);

            throw $exception;
        }
    }

    /**
     * @return array{name: ?string, url: ?string, quantity: int, rating: ?float, notes: ?string}
     */
    private function itemAttributes(CollectionItem|WishlistItem $item): array
    {
        return [
            'name' => $item->name,
            'url' => $item->url,
            'quantity' => $item->quantity,
            'rating' => $item->rating,
            'notes' => $item->notes,
        ];
    }

    private function moveImage(string $sourcePath, string $directory): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $destinationPath = $directory.'/'.Str::uuid().($extension !== '' ? ".{$extension}" : '');

        if (! Storage::disk('public')->move($sourcePath, $destinationPath)) {
            throw new \RuntimeException('The item photo could not be moved.');
        }

        return $destinationPath;
    }
}
