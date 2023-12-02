<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingSchedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'subscription_id',
        'day',
        'time',
    ];
    public function subscription(){
        return $this->belongsTo(Subscription::class);
    }
}
