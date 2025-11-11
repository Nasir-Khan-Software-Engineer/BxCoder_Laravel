<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'code' => $this->code,
            'details' => $this->details,
            'keywords' => $this->keywords,
            'short_description' => $this->short_description,
            'status' => $this->status,
            'is_active' => $this->is_active,

            // Brand
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                ];
            }),

            // Unit
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name ?? null,
                ];
            }),

            // Creator & updater
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            'updater' => $this->whenLoaded('updater', function () {
                return $this->updater ? [
                    'id' => $this->updater->id,
                    'name' => $this->updater->name,
                ] : null;
            }),

            // Categories
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                });
            }),

            // Images
            'images' => $this->whenLoaded('images', function () {
                return $this->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'image' => $image->image,
                        'alt' => $image->alt,
                        'title' => $image->title,
                        'style' => $image->style,
                    ];
                });
            }),

            // Stocks
            'stocks' => $this->whenLoaded('stocks', function () {
                return $this->stocks->map(function ($stock) {
                    return [
                        'id' => $stock->id,
                        'quantity' => $stock->quantity,
                        'buying_price' => $stock->buying_price,
                        'selling_price' => $stock->selling_price,
                        'discount_type' => $stock->discount_type,
                        'discount_value' => $stock->discount_value,
                        'supplier_id' => $stock->supplier_id,
                        'is_active' => $stock->is_active,
                    ];
                });
            }),

            // Reviews
            'reviews' => $this->whenLoaded('reviews', function () {
                return $this->reviews->map(function ($review) {
                    return [
                        'id' => $review->id,
                        'user_id' => $review->user_id,
                        'rating' => $review->rating,
                        'comment' => $review->comment,
                        'status' => $review->status,
                    ];
                });
            }),

            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
