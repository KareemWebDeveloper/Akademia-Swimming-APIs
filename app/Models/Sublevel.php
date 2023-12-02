<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sublevel extends Model
{
    use HasFactory;
    protected $fillable = [
        'sublevel_name',
        'level_id',
        'level_description',
    ];
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function checkpoints()
    {
        return $this->hasMany(Checkpoint::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
