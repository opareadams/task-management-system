<?php

namespace App\Http\Controllers\Member;

use App\Helper\Reply;
use App\Http\Requests\TimeLogs\StartTimer;
use App\Http\Requests\TimeLogs\StoreTimeLog;
use App\ModuleSetting;
use App\Notifications\TimerStarted;
use App\Project;
use App\ProjectMember;
use App\ProjectTimeLog;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\Datatables\Facades\Datatables;

class MemberTimeLogController extends MemberBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.projects');

        if(!ModuleSetting::checkModule('timelogs')){
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->projects = ProjectMember::where('user_id', $this->user->id)->get();
        return view('member.time-log.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StartTimer $request)
    {
        $timeLog = new ProjectTimeLog();
        $timeLog->project_id = $request->project_id;
        $timeLog->user_id = $this->user->id;
        $timeLog->start_time = Carbon::now();
        $timeLog->memo = $request->memo;
        $timeLog->save();

        $this->logProjectActivity($request->project_id, __('messages.timerStartedBy').' '.ucwords($timeLog->user->name));
        $this->logUserActivity($this->user->id, __('messages.timerStartedProject').ucwords($timeLog->project->project_name));

        return Reply::successWithData(__('messages.timerStartedSuccessfully'), ['html' => '<div class="nav navbar-top-links navbar-right pull-right m-t-10">
                        <a class="btn btn-rounded btn-default stop-timer-modal" href="javascript:;" data-timer-id="'.$timeLog->id .'">
                            <i class="ti-alarm-clock"></i>
                            <span id="active-timer">'.$timeLog->timer.'</span>
                            <label class="label label-danger">'.__("app.stop").'</label></a>
                    </div>']);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->timeLog = ProjectTimeLog::findOrFail($id);
        return view('member.time-log.show', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showTomeLog($id)
    {
        $this->project = Project::findOrFail($id);
        return view('member.time-log.show-log', $this->data);
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
        $timeId = $request->timeId;
        $timeLog = ProjectTimeLog::findOrFail($timeId);
        $timeLog->end_time = Carbon::now();
        $timeLog->save();

        $timeLog->total_hours = $timeLog->end_time->diff($timeLog->start_time)->format('%d')*24+$timeLog->end_time->diff($timeLog->start_time)->format('%H');
        $timeLog->total_minutes = ($timeLog->total_hours*60)+($timeLog->end_time->diff($timeLog->start_time)->format('%i'));
        $timeLog->edited_by_user = $this->user->id;
        $timeLog->save();

        $this->logProjectActivity($timeLog->project_id, __('messages.timerStoppedBy').' '.ucwords($timeLog->user->name));

        return Reply::successWithData(__('messages.timerStoppedSuccessfully'), ['html' => '<div class="nav navbar-top-links navbar-right pull-right m-t-10">
                        <a class="btn btn-rounded btn-default timer-modal" href="javascript:;">'.__("modules.timeLogs.startTimer").' <i class="fa fa-check-circle text-success"></i></a>
                    </div>']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    /**
     * @param $id
     * @return mixed
     */
    public function data($id)
    {
        $timeLogs = ProjectTimeLog::where('project_id', $id)->get();

        return Datatables::of($timeLogs)
            ->addColumn('action', function($row){
                $action = '';
                if($this->user->can('edit_timelogs')){
                    $action.= '<a href="javascript:;" class="btn btn-info btn-circle edit-time-log"
                      data-toggle="tooltip" data-time-id="'.$row->id.'"  data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }
                if($this->user->can('delete_timelogs')){
                    $action.= '&nbsp;<a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-time-id="'.$row->id.'" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }
                return $action;
            })
            ->editColumn('start_time', function($row){
                return $row->start_time->timezone($this->global->timezone)->format('d M, Y h:i A');
            })
            ->editColumn('end_time', function($row){
                if(!is_null($row->end_time)){
                    return $row->end_time->timezone($this->global->timezone)->format('d M, Y h:i A');
                }
                else{
                    return "<label class='label label-success'>".__('app.active')."</label>";
                }
            })
            ->editColumn('user_id', function($row){
                return ucwords($row->user->name);
            })
            ->editColumn('edited_by_user', function($row){
                if(!is_null($row->edited_by_user)){
                    return ucwords($row->editor->name);
                }
            })
            ->editColumn('total_hours', function($row){
                $timeLog = intdiv($row->total_minutes, 60).' hrs ';

                if(($row->total_minutes % 60) > 0){
                    $timeLog.= ($row->total_minutes % 60).' mins';
                }

                return $timeLog;
            })
            ->rawColumns(['end_time', 'action'])
            ->removeColumn('project_id')
            ->make(true);
    }
}
