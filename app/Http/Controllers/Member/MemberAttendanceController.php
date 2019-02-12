<?php

namespace App\Http\Controllers\Member;

use App\Attendance;
use App\AttendanceSetting;
use App\Helper\Reply;
use App\Http\Requests\Attendance\StoreAttendance;
use App\ModuleSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;

class MemberAttendanceController extends MemberBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-clock';
        $this->pageTitle = __('app.menu.attendance');

        if(!ModuleSetting::checkModule('attendance')){
            abort(403);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendanceSettings = AttendanceSetting::first();
        $openDays = json_decode($attendanceSettings->office_open_days);
        $this->startDate = Carbon::today()->timezone($this->global->timezone)->startOfMonth();
        $this->endDate = Carbon::today()->timezone($this->global->timezone);
        $this->employees = User::allEmployees();
        $this->userId = $this->user->id;

        $this->totalWorkingDays = $this->startDate->diffInDaysFiltered(function(Carbon $date) use ($openDays){
            foreach($openDays as $day){
                if($date->dayOfWeek == $day){
                    return $date;
                }
            }
        }, $this->endDate);
        $this->daysPresent = Attendance::countDaysPresentByUser($this->startDate, $this->endDate, $this->userId);
        $this->daysLate = Attendance::countDaysLateByUser($this->startDate, $this->endDate, $this->userId);
        $this->halfDays = Attendance::countHalfDaysByUser($this->startDate, $this->endDate, $this->userId);

        $this->todayAttendance = Attendance::where(DB::raw('DATE(clock_in_time)'), Carbon::today()->format('Y-m-d'))
            ->where('user_id', $this->user->id)->first();
        return view('member.attendance.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if(!$this->user->can('add_attendance')){
            abort(403);
        }
        return view('member.attendance.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $attendanceSettings = AttendanceSetting::first();
        $now = Carbon::now();
        $timestamp = $now->format('Y-m-d').' '.$attendanceSettings->office_start_time;
        $officeStartTime = Carbon::createFromFormat('Y-m-d H:i:s', $timestamp, $this->global->timezone);
        $officeStartTime = $officeStartTime->setTimezone('UTC');

        $lateTime = $officeStartTime->addMinutes($attendanceSettings->late_mark_duration);

        $attendance = new Attendance();
        $attendance->user_id = $this->user->id;
        $attendance->clock_in_time = $now;
        $attendance->clock_in_ip = request()->ip();
        if(is_null($request->working_from)){
            $attendance->working_from = 'office';
        }
        else{
            $attendance->working_from = $request->working_from;
        }

        if($now->gt($lateTime)){
            $attendance->late = 'yes';
        }

        $attendance->half_day = 'no';
        $attendance->save();

        return Reply::successWithData(__('messages.attendanceSaveSuccess'), ['time' => $now->format('h:i A'), 'ip' => $attendance->clock_in_ip, 'working_from' => $attendance->working_from]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $now = Carbon::now();

        $attendance = Attendance::findOrFail($id);
        $attendance->clock_out_time = $now;
        $attendance->clock_out_ip = request()->ip();
        $attendance->save();

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Attendance::destroy($id);
        return Reply::success(__('messages.attendanceDelete'));
    }

    public function refreshCount($startDate = null, $endDate = null, $userId = null){
        $attendanceSettings = AttendanceSetting::first();
        $openDays = json_decode($attendanceSettings->office_open_days);
        $startDate = Carbon::createFromFormat('!Y-m-d', $startDate);
        $endDate = Carbon::createFromFormat('!Y-m-d', $endDate)->addDay(1);

        $totalWorkingDays = $startDate->diffInDaysFiltered(function(Carbon $date) use ($openDays){
            foreach($openDays as $day){
                if($date->dayOfWeek == $day){
                    return $date;
                }
            }
        }, $endDate);
        $daysPresent = Attendance::countDaysPresentByUser($startDate, $endDate, $userId);
        $daysLate = Attendance::countDaysLateByUser($startDate, $endDate, $userId);
        $halfDays = Attendance::countHalfDaysByUser($startDate, $endDate, $userId);
        $daysAbsent = (($totalWorkingDays - $daysPresent) < 0) ? '0' : ($totalWorkingDays - $daysPresent);

        return Reply::dataOnly(['daysPresent' => $daysPresent, 'daysLate' => $daysLate, 'halfDays' => $halfDays, 'totalWorkingDays' => $totalWorkingDays, 'absentDays' => $daysAbsent]);

    }

    public function employeeData($startDate = null, $endDate = null, $userId = null){
        $attendances = Attendance::userAttendanceByDate($startDate, $endDate, $userId);
        $presentDates = $attendances->pluck('clock_in_date');
        $startDate = Carbon::createFromFormat('!Y-m-d', $startDate);
        $endDate = Carbon::createFromFormat('!Y-m-d', $endDate);
        $view = view('member.attendance.user_attendance', ['attendances' => $attendances, 'startDate' => $startDate, 'endDate' => $endDate, 'presentDates' => $presentDates, 'global' => $this->global, 'user' => $this->user])->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);

    }

    public function data(Request $request){
        $date = Carbon::createFromFormat('m/d/Y', $request->date)->format('Y-m-d');
        $attendances = Attendance::attendanceByDate($date);

        return Datatables::of($attendances)
            ->edit_column('id', function ($row) {
                return view('member.attendance.attendance_list', ['row' => $row, 'global' => $this->global])->render();
            })
            ->remove_column('name')
            ->remove_column('clock_in_time')
            ->remove_column('clock_out_time')
            ->remove_column('image')
            ->remove_column('attendance_id')
            ->remove_column('working_from')
            ->remove_column('late')
            ->remove_column('half_day')
            ->make();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAttendance(StoreAttendance $request)
    {
        $date = Carbon::createFromFormat('m/d/Y', $request->date)->format('Y-m-d');
        $clockIn = Carbon::createFromFormat('h:i A', $request->clock_in_time, $this->global->timezone);
        $clockIn->setTimezone('UTC');
        $clockIn = $clockIn->format('H:i:s');
        if($request->clock_out_time != ''){
            $clockOut = Carbon::createFromFormat('h:i A', $request->clock_out_time, $this->global->timezone);
            $clockOut->setTimezone('UTC');
            $clockOut = $clockOut->format('H:i:s');
            $clockOut = $date.' '.$clockOut;
        }
        else{
            $clockOut = null;
        }

        $attendance = Attendance::where('user_id', $request->user_id)->where(DB::raw('DATE(`clock_in_time`)'), $date)->first();
        if(!is_null($attendance)){
            $attendance->update([
                'user_id' => $request->user_id,
                'clock_in_time' => $date.' '.$clockIn,
                'clock_in_ip' => $request->clock_in_ip,
                'clock_out_time' => $clockOut,
                'clock_out_ip' => $request->clock_out_ip,
                'working_from' => $request->working_from,
                'late' => $request->late,
                'half_day' => $request->half_day
            ]);
        }else{
            Attendance::create([
                'user_id' => $request->user_id,
                'clock_in_time' => $date.' '.$clockIn,
                'clock_in_ip' => $request->clock_in_ip,
                'clock_out_time' => $clockOut,
                'clock_out_ip' => $request->clock_out_ip,
                'working_from' => $request->working_from,
                'late' => $request->late,
                'half_day' => $request->half_day
            ]);
        }

        return Reply::success(__('messages.attendanceSaveSuccess'));
    }
}
