<?php

namespace App\Http\Ressources;

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
            'order_id' => $this->order_id,
            'uid' => $this->uid,
            'order_date' => $this->order_date,
            'total_amount' => $this->total_amount,
            'status' => $this->status,
            'user' => $this->whenLoaded('user', fn () => new UserResource($this->user)),
            'order_items' => OrderItemResource::collection($this->whenLoaded('orderItems')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
