<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $dates = ['paid_on'];

    public function invoice() {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
