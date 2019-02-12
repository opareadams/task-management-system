<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\ModuleSetting;
use App\Project;
use App\ProjectTimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;

class ManageAllTimeLogController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Time Logs';
        $this->pageIcon = 'icon-clock';

        if(!ModuleSetting::checkModule('timelogs')){
            abort(403);
        }
    }

    public function index(){
        $this->projects = Project::all();
        $this->activeTimers = ProjectTimeLog::whereNull('end_time')
            ->get();
        return view('admin.time-logs.index', $this->data);
    }

    public function data($startDate = null, $endDate = null, $projectId = null) {
        $timeLogs = ProjectTimeLog::join('projects', 'projects.id', '=', 'project_time_logs.project_id')
            ->join('users', 'users.id', '=', 'project_time_logs.user_id')
            ->select('project_time_logs.id', 'projects.project_name', 'project_time_logs.start_time', 'project_time_logs.end_time', 'project_time_logs.total_hours', 'project_time_logs.total_minutes', 'project_time_logs.memo', 'project_time_logs.user_id', 'project_time_logs.project_id', 'users.name');

        if(!is_null($startDate)){
            $timeLogs->where(DB::raw('DATE(project_time_logs.`start_time`)'), '>=', $startDate);
        }

        if(!is_null($endDate)){
            $timeLogs->where(DB::raw('DATE(project_time_logs.`end_time`)'), '<=', $endDate);
        }

        if($projectId != 0){
            $timeLogs->where('project_time_logs.project_id', '=', $projectId);
        }

        $timeLogs->get();

        return Datatables::of($timeLogs)
            ->addColumn('action', function($row){
                return '<a href="javascript:;" class="btn btn-info btn-circle edit-time-log"
                      data-toggle="tooltip" data-time-id="'.$row->id.'"  data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                        data-toggle="tooltip" data-time-id="'.$row->id.'" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
            })
            ->editColumn('name', function($row){
                return '<a href="'.route('admin.employees.show', $row->user_id).'" target="_blank" >'.ucwords($row->name).'</a>';
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
            ->editColumn('total_hours', function($row){
                $timeLog = intdiv($row->total_minutes, 60).' hrs ';

                if(($row->total_minutes % 60) > 0){
                    $timeLog.= ($row->total_minutes % 60).' mins';
                }

                return $timeLog;
            })
            ->editColumn('project_name', function ($row) {
                return '<a href="' . route('admin.projects.show', $row->project_id) . '">' . ucfirst($row->project_name) . '</a>';
            })
            ->rawColumns(['end_time', 'action', 'project_name', 'name'])
            ->removeColumn('project_id')
            ->removeColumn('total_minutes')
            ->make(true);
    }

    public function destroy($id) {
        ProjectTimeLog::destroy($id);
        return Reply::success(__('messages.timeLogDeleted'));
    }

    /**
     * @param Request $request
     * @return array
     */
    public function stopTimer(Request $request){
        $timeId = $request->timeId;
        $timeLog = ProjectTimeLog::findOrFail($timeId);
        $timeLog->end_time = Carbon::now();
        $timeLog->edited_by_user = $this->user->id;
        $timeLog->save();

        $timeLog->total_hours = ($timeLog->end_time->diff($timeLog->start_time)->format('%d')*24)+($timeLog->end_time->diff($timeLog->start_time)->format('%H'));

        if($timeLog->total_hours == 0){
            $timeLog->total_hours = round(($timeLog->end_time->diff($timeLog->start_time)->format('%i')/60),2);
        }
        $timeLog->total_minutes = ($timeLog->total_hours*60)+($timeLog->end_time->diff($timeLog->start_time)->format('%i'));

        $timeLog->save();

        $this->activeTimers = ProjectTimeLog::whereNull('end_time')
            ->get();
        $view = view('admin.projects.time-logs.active-timers', $this->data)->render();
        return Reply::successWithData(__('messages.timerStoppedSuccessfully'), ['html' => $view]);
    }


}
