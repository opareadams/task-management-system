<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeTeam2 extends Model
{
    protected $table = 'employee_teams2';
    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }
}
