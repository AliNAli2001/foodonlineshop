<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->get('lang', 'en'); // default en
        $isImagesLoaded = $this->relationLoaded('images');
        return [
            'id' => $this->id,
            'name' => $this->getName($lang),
            'description' => $this->getDescription($lang),
            'price' => $this->selling_price,
            'featured' => (bool) $this->featured,
            'in_stock' => $this->isInStock(),
            'available_quantity' => $this->stock_available_quantity,

            $this->mergeWhen(!$isImagesLoaded, [
                'image' => $this->primaryImage?->image_url,
            ]),
            $this->mergeWhen($isImagesLoaded, [
                'images' => $this->images
                    ->sortByDesc('is_primary') // Primary first
                    ->values()
                    ->map(function ($image) {
                        return [
                            'url' => $image->image_url,
                            'is_primary' => $image->is_primary,
                            'caption' => $image->caption, // include if useful, remove if not needed
                            // 'id' => $image->id, // optional: if frontend needs to reference
                        ];
                    }),
            ]),
            'category' => $this->whenLoaded('category', function () use ($lang) {
                return [
                    'id' => $this->category->id,
                    'name' => $lang === 'ar'
                        ? $this->category->name_ar
                        : $this->category->name_en,
                ];
            }),

            'company' => $this->whenLoaded('company', function () use ($lang) {
                return [
                    'id' => $this->company->id,
                    'name' => $lang === 'ar'
                        ? $this->company->name_ar
                        : $this->company->name_en,
                ];
            }),

            // ✅ Tags in requested language
            'tags' => $this->whenLoaded('tags', function () use ($lang) {
                return $this->tags->map(fn($tag) => [
                    'id' => $tag->id,
                    'name' => $tag->getName($lang),
                ])->values();
            }),
        ];
    }
}
