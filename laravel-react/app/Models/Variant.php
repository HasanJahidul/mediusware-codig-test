<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];
    // product variant
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'variant_id');
    }

}
