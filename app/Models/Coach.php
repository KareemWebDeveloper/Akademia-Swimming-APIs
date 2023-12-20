<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Coach extends Model
{
    use HasFactory , HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'address',
        'password',
        'phone',
        'salary_per_hour',
        'hours_worked',
        'last_paid_date',
        'advance_payment',
        'salary_discount',
    ];
    public function branches(){
        return $this->belongsToMany(Branch::class, 'coach_branch');
    }
    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class)->whereIn('state', ['active', 'frozen']);
    }
    public function subscriptions(){
        return $this->hasMany(Subscription::class);
    }
    public function salaries(){
        return $this->hasMany(Salary::class);
    }
    public function attendances(){
        return $this->hasMany(Attendance::class);
    }
}
