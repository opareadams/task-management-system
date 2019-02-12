<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Team2 extends Model
{
    protected $table = 'teams2';
    public function members()
    {
        return $this->hasMany(EmployeeTeam2::class, 'team2_id');
    }
}
