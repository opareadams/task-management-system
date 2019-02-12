<?php

namespace App\Http\Controllers\Admin;

use App\Attendance;
use App\AttendanceSetting;
use App\EmployeeDetails;
use App\Helper\Reply;
use App\Http\Requests\Attendance\StoreAttendance;
use App\Leave;
use App\ModuleSetting;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;

class ManageAttendanceController extends AdminBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.attendance');
        $this->pageIcon = 'icon-clock';

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
        $this->userId = User::first()->id;

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
        return view('admin.attendance.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.attendance.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAttendance $request)
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
        //
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

    public function data(Request $request){
        $date = Carbon::createFromFormat('m/d/Y', $request->date)->format('Y-m-d');
        $attendances = Attendance::attendanceByDate($date);

        return Datatables::of($attendances)
            ->edit_column('id', function ($row) {
                return view('admin.attendance.attendance_list', ['row' => $row, 'global' => $this->global])->render();
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

    public function refreshCount($startDate = null, $endDate = null, $userId = null){
        $attendanceSettings = AttendanceSetting::first();
        $openDays = json_decode($attendanceSettings->office_open_days);
        $startDate = Carbon::createFromFormat('!Y-m-d', $startDate);
        $endDate = Carbon::createFromFormat('!Y-m-d', $endDate)->addDay(1); //addDay(1) is hack to include end date

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
        $leavesDates = Leave::where('user_id', $userId)
            ->where('leave_date', '>=', $startDate)
            ->where('leave_date', '<=', $endDate)
            ->where('status', 'approved')
            ->select('leave_date')
            ->get();
        $leaves = [];

        foreach ($leavesDates as $leavesDate) {
            array_push($leaves, $leavesDate->leave_date->format('Y-m-d'));
        }

        $view = view('admin.attendance.user_attendance',
            [
                'attendances' => $attendances, 'startDate' => $startDate,
                'endDate' => $endDate, 'presentDates' => $presentDates,
                'global' => $this->global, 'leavesDate' => $leaves
            ]
        )->render();

        return Reply::dataOnly(['status' => 'success', 'data' => $view]);

    }

    public function attendanceByDate(){
        return view('admin.attendance.by_date', $this->data);
    }


    public function byDateData(Request $request){
        $date = Carbon::createFromFormat('m/d/Y', $request->date)->format('Y-m-d');
        $attendances = Attendance::attendanceByDate($date);

        return Datatables::of($attendances)
            ->edit_column('id', function ($row) {
                return view('admin.attendance.attendance_by_date_list', ['row' => $row, 'global' => $this->global])->render();
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

    public function dateAttendanceCount(Request $request){
        $date = Carbon::createFromFormat('m/d/Y', $request->date)->format('Y-m-d');

        $totalEmployees = count(User::allEmployees());
        $totalPresent = Attendance::where(DB::raw('DATE(`clock_in_time`)'), '=', $date)->count();
        $totalAbsent = ($totalEmployees-$totalPresent);

        return Reply::dataOnly(['status' => 'success', 'totalEmployees' => $totalEmployees, 'totalPresent' => $totalPresent, 'totalAbsent' => $totalAbsent]);
    }

}
