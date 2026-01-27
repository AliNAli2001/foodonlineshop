<?php

namespace App\Http\Resources\Client;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
     public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'status_label' => \App\Models\Order::STATUSES[$this->status] ?? null,

            'order_date' => $this->order_date?->format('Y-m-d H:i'),

            'total_amount' => $this->total_amount,

            'delivery_method' => $this->delivery_method,
            'delivery_method_label' => \App\Models\Order::DELIVERY_METHODS[$this->delivery_method] ?? null,

            'address' => $this->address_details,

            'location' => $this->when($this->latitude && $this->longitude, [
                'lat' => $this->latitude,
                'lng' => $this->longitude,
            ]),

            'notes' => $this->general_notes,

            // Delivery – only include if the relation was eager-loaded
            'delivery'   => $this->whenLoaded('delivery', function () {
                return new DeliveryResource($this->delivery);
            }),

            'items' => OrderItemResource::collection($this->whenLoaded('items')),
        ];
    }
}
