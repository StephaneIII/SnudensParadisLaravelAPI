<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['attributes_name', 'description', 'data_type'])]
class Attributes extends Model
{
    protected $primaryKey = 'attributes_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected function casts(): array
    {
        return [
            'attributes_id' => 'int',
            'attributes_name' => 'string',
            'description' => 'string',
            'data_type' => 'string',
        ];
    }

    /**
     * Get all attribute values for this attribute.
     */
    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributesValue::class, 'attributes_id', 'attributes_id');
    }
}