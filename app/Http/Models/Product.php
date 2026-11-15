<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'rating', 'price', 'description', 'mærke'])]
class Product extends Model
{
    protected $primaryKey = 'product_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    /**
     * Get all order items for this product.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    /**
     * Get all attribute values for this product.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributesValue::class, 'product_id', 'product_id');
    }
}