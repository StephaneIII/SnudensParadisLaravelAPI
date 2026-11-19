<?php

namespace App\Http\Ressources;

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
        return [
            'product_id' => $this->product_id,
            'name' => $this->name,
            'rating' => $this->rating,
            'price' => $this->price,
            'description' => $this->description,
            'mærke' => $this->{'mærke'},
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'attribute_values' => AttributesValueResource::collection($this->whenLoaded('attributeValues')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
