<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'coach_id',
        'category_id',
        'branch_id',
        'category_name',
        'subscription_date',
        'expiration_date',
        'freeze_start_date',
        'freeze_end_date',
        'avail_freeze_days',
        'academy_id',
        'academy_name',
        'number_of_sessions',
        'sessions_per_week',
        'subscription_type',
        'sale',
        'state',
        'isfrozen',
        'is_private',
        'price',
        'created_by',
        'invitations',
    ];

    public function customer(){
        return $this->belongsTo(Customer::class);
    }
    public function branch(){
        return $this->belongsTo(Branch::class);
    }
    public function coach(){
        return $this->belongsTo(Coach::class);
    }
    public function academy(){
        return $this->belongsTo(Academy::class);
    }
    public function attendances(){
        return $this->hasMany(Attendance::class);
    }
    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function trainingSchedules(){
        return $this->hasMany(TrainingSchedule::class);
    }

    public function installments(){
        return $this->hasMany(Installment::class);
    }
}
