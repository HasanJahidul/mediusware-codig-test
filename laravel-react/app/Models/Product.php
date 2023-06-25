<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function prices()
    {
        return $this->hasMany(ProductVariantPrice::class);
    }
    public function product_variants()
    {
        return $this->hasMany(ProductVariant::class);
    }
}
