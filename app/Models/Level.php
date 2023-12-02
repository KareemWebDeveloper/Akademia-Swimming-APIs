<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;
    protected $fillable = [
        'level_name',
        'level_description',
    ];
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function sublevels()
    {
        return $this->hasMany(Sublevel::class);
    }
    public function sublevelCount()
    {
        return $this->sublevels()->count();
    }

}
