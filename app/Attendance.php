<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Attendance extends Model
{
    protected $dates = ['clock_in_time', 'clock_out_time'];
    protected $appends = ['clock_in_date'];

    public function user(){
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScopes(['active']);
    }

    public function getClockInDateAttribute()
    {
        return $this->clock_in_time->toDateString();
    }

    public static function attendanceByDate($date) {
        return User::leftJoin(
                'attendances', function ($join) use ($date) {
                    $join->on('users.id', '=', 'attendances.user_id')
                        ->where(DB::raw('DATE(attendances.clock_in_time)'), '=', $date);
                }
            )
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->leftJoin('employee_details', 'employee_details.user_id', '=', 'users.id')
            ->where('roles.name', '<>', 'client')
            ->select(
                'users.id',
                'users.name',
                'attendances.clock_in_ip',
                'attendances.clock_in_time',
                'attendances.clock_out_time',
                'attendances.late',
                'attendances.half_day',
                'attendances.working_from',
                'users.image',
                'employee_details.job_title',
                'attendances.id as attendance_id'
            )
            ->groupBy('users.id')
            ->orderBy('users.name', 'asc');
    }

    public static function userAttendanceByDate($startDate, $endDate, $userId) {
        return Attendance::join('users', 'users.id', '=', 'attendances.user_id')
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('attendances.user_id', '=', $userId)
            ->orderBy('attendances.id', 'desc')
            ->select('attendances.*', 'users.*', 'attendances.id as aId')
            ->get();
    }

    public static function countDaysPresentByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->count();
    }

    public static function countDaysLateByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->where('late', 'yes')
            ->count();
    }

    public static function countHalfDaysByUser($startDate, $endDate, $userId){
        return Attendance::where(DB::raw('DATE(attendances.clock_in_time)'), '>=', $startDate)
            ->where(DB::raw('DATE(attendances.clock_in_time)'), '<=', $endDate)
            ->where('user_id', $userId)
            ->where('half_day', 'yes')
            ->count();
    }

    protected $guarded = ['id'];
}
