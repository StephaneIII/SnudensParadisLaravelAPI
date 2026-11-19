<?php

namespace App\Http\Ressources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'amount' => $this->amount,
            'order' => $this->whenLoaded('order', fn () => new OrderResource($this->order)),
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
