<?php

namespace App\Http\Ressources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributesResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes_id' => $this->attributes_id,
            'attributes_name' => $this->attributes_name,
            'description' => $this->description,
            'data_type' => $this->data_type,
            'attribute_values' => AttributesValueResource::collection($this->whenLoaded('attributeValues')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
