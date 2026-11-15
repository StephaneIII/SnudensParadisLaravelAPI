<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'attributes_id', 'value', 'min_value', 'max_value'])]
class AttributesValue extends Model
{
    protected $primaryKey = ['product_id', 'attributes_id'];
    public $incrementing = false;
    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'product_id' => 'int',
            'attributes_id' => 'int',
            'value' => 'string',
            'min_value' => 'decimal:3',
            'max_value' => 'decimal:3',
        ];
    }

    /**
     * Get the product for this attribute value.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Get the attribute for this attribute value.
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attributes::class, 'attributes_id', 'attributes_id');
    }
}