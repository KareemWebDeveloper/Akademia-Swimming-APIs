<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Employee extends Model
{
    use HasFactory , HasApiTokens;
    protected $fillable = [
        'name',
        'email',
        'address',
        'password',
        'type',
        'phone',
        'salary',
        'last_paid_date',
        'advance_payment',
        'salary_discount',
    ];
    public function branches(){
        return $this->belongsToMany(Branch::class, 'employee_branch');
    }
    public function roles(){
        return $this->belongsToMany(Role::class, 'employee_role');
    }
    public function salaries(){
        return $this->hasMany(Salary::class);
    }
}
