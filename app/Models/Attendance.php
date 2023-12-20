<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $fillable = [
        'customer_id',
        'subscription_id',
        'coach_id',
        'branch_id',
        'category_id',
        'training_start_time',
        'training_end_time',
        'session_duration',
        'is_attended',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function branch(){
        return $this->belongsTo(Branch::class);
    }
    public function subscription(){
        return $this->belongsTo(Subscription::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }
    public function coach(){
        return $this->belongsTo(Coach::class);
    }

}
