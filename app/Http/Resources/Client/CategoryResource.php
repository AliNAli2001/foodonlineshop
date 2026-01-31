<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     public function toArray(Request $request): array
    {
        $lang = $request->get('lang', 'en');

        return [
            'id' => $this->id,
            'name' => $lang === 'ar' ? $this->name_ar : $this->name_en,

            'image' => $this->full_url,

            'featured' => (bool) $this->featured,
// Only include products_count if it was loaded
            $this->mergeWhen(isset($this->products_count), [
                'products_count' => $this->products_count,
            ]),
            // 🔥 Automatically format products if loaded
            'products' => ProductResource::collection(
                $this->whenLoaded('products')
            ),
        ];
    }
}
