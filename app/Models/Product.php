<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_name',
        'product_price',
        'product_sale',
        'product_cost',
        'product_count',
        'product_image',
        'product_section_id',
    ];
    public function ProductSection(){
        return $this->belongsTo(ProductSections::class);
    }
}
