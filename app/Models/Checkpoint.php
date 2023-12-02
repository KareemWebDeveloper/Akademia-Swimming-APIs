<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Checkpoint extends Model
{
    use HasFactory;
    protected $fillable = [
        "sublevel_id",
        "checkpoint_name",
        "checkpoint_description",
        "passed",
    ];
    public function sublevel()
    {
        return $this->belongsTo(Sublevel::class);
    }
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
