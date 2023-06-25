<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariantPrice extends Model
{
    protected $fillable = [
        'product_variant_one',
        'product_variant_two',
        'product_variant_three',
        'price',
        'stock',
        'product_id',
        
    ];

    public function product_variant_1()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_one');
    }
    public function product_variant_2()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_two');
    }
    public function product_variant_3()
    {
        return $this->belongsTo(ProductVariant::class,'product_variant_three');
    }

}
