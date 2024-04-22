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
    public function activeSubscriptions(){
        return $this->hasMany(Subscription::class)->whereIn('state', ['active', 'frozen'])->count();
    }
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'academy_branch');
    }
    public function branchesCount()
    {
        return $this->branches()->count();
    }
}
