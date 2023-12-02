<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_name',
    ];

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_category')->withPivot('price_per_session', 'duration' , 'price_per_1' , 'price_per_2'
        , 'price_per_4' , 'price_per_6' , 'price_per_8');
    }
}
