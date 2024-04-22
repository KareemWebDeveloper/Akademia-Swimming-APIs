<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'coach_id',
        'employee_id',
        'amount',
        'paid_date',
        'hours_worked',
        'branch_id',
        'bonus',
        'discount',
        'notes',
        'from_date',
    ];

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
