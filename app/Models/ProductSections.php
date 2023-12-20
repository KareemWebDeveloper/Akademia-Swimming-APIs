<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSections extends Model
{
    use HasFactory;
    protected $fillable = [
        'section_name'
    ];
    public function products(){
        return $this->hasMany(Product::class , 'product_section_id');
    }
}
