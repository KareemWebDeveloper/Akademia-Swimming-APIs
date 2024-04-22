<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasFactory , HasApiTokens;

    protected $fillable = [
        'customer_name',
        'customer_email',
        'customer_address',
        'level_id',
        'sublevel_id',
        'checkpoint_id',
        'birthdate',
        'customer_phone',
        'last_attendance_date',
        'attached_academy_id',
        'attached_branch_id',
        'gender',
        'job',
    ];

    public function subscriptions(){
        return $this->hasMany(Subscription::class);
    }
    public function orders(){
        return $this->hasMany(Order::class);
    }
    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class)->whereIn('state', ['active', 'frozen']);
    }
    public function unpaidInstallments()
    {
        return $this->hasMany(Installment::class)->where('paid', 0);
    }
    public function installments(){
        return $this->hasMany(Installment::class);
    }
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function sublevel()
    {
        return $this->belongsTo(Sublevel::class);
    }

    public function checkpoint()
    {
        return $this->belongsTo(Checkpoint::class);
    }

    public function attendances(){
        return $this->hasMany(Attendance::class);
    }

}
