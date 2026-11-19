<?php

namespace App\Http\Ressources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributesValueResource extends JsonResource
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
            'attributes_id' => $this->attributes_id,
            'value' => $this->value,
            'min_value' => $this->min_value,
            'max_value' => $this->max_value,
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'attribute' => $this->whenLoaded('attribute', fn () => new AttributesResource($this->attribute)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
