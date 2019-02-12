<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Estimate extends Model
{
    use Notifiable;

    protected $dates = ['valid_till'];

    public function items() {
        return $this->hasMany(EstimateItem::class, 'estimate_id');
    }

    public function client(){
        return $this->belongsTo(User::class, 'client_id')->withoutGlobalScopes(['active']);
    }

    public function currency(){
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}
