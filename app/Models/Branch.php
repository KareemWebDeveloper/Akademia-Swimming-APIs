<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_name',
    ];
    public function coaches()
    {
        return $this->belongsToMany(Coach::class, 'coach_branch');
    }
    public function academies()
    {
        return $this->belongsToMany(Academy::class, 'academy_branch');
    }
    public function workingDays()
    {
        return $this->hasMany(WorkingDay::class);
    }
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'employee_branch');
    }
    public function categories()
    {
        return $this->belongsToMany(Category::class, 'branch_category')->withPivot('price_per_session', 'duration' , 'session_prices');
    }
}
