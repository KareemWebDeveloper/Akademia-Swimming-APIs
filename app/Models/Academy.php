<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Academy extends Model
{
    use HasFactory;
    protected $fillable = [
        'academy_name',
    ];
    public function subscriptions(){
        return $this->hasMany(Subscription::class);
    }
}
