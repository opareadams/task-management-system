<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Project;
use App\Task;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\Datatables\Facades\Datatables;

class TaskReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.taskReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->projects = Project::all();
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();
        $this->employees = User::allEmployees();

        $this->totalTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $this->fromDate->format('Y-m-d'))
            ->where(DB::raw('DATE(`due_date`)'), '<=', $this->toDate->format('Y-m-d'))
            ->count();

        $this->completedTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $this->fromDate->format('Y-m-d'))
            ->where(DB::raw('DATE(`due_date`)'), '<=', $this->toDate->format('Y-m-d'))
            ->where('status', 'completed')
            ->count();

        $this->pendingTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $this->fromDate->format('Y-m-d'))
            ->where(DB::raw('DATE(`due_date`)'), '<=', $this->toDate->format('Y-m-d'))
            ->where('status', 'incomplete')
            ->count();

        return view('admin.reports.tasks.index', $this->data);
    }

    public function store(Request $request){

        $totalTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $request->startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $request->endDate);

        if(!is_null($request->projectId)){
            $totalTasks->where('project_id', $request->projectId);
        }

        if(!is_null($request->employeeId)){
            $totalTasks->where('user_id', $request->employeeId);
        }

        $totalTasks = $totalTasks->count();

        $completedTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $request->startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $request->endDate);

        if(!is_null($request->projectId)){
            $completedTasks->where('project_id', $request->projectId);
        }

        if(!is_null($request->employeeId)){
            $completedTasks->where('user_id', $request->employeeId);
        }
        $completedTasks = $completedTasks->where('status', 'completed')->count();

        $pendingTasks = Task::where(DB::raw('DATE(`due_date`)'), '>=', $request->startDate)
            ->where(DB::raw('DATE(`due_date`)'), '<=', $request->endDate);

        if(!is_null($request->projectId)){
            $pendingTasks->where('project_id', $request->projectId);
        }

        if(!is_null($request->employeeId)){
            $pendingTasks->where('user_id', $request->employeeId);
        }

        $pendingTasks = $pendingTasks->where('status', 'incomplete')->count();

        return Reply::successWithData(__('messages.reportGenerated'),
            ['pendingTasks' => $pendingTasks, 'completedTasks' => $completedTasks, 'totalTasks' => $totalTasks]
        );
    }

    public function data($startDate = null, $endDate = null, $employeeId = null, $projectId = null) {
        $tasks = Task::leftJoin('projects', 'projects.id', '=', 'tasks.project_id')
            ->join('users', 'users.id', '=', 'tasks.user_id')
            ->select('tasks.id', 'projects.project_name', 'tasks.heading', 'users.name', 'users.image', 'tasks.due_date', 'tasks.status', 'tasks.project_id');

        if(!is_null($startDate)){
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '>=', $startDate);
        }

        if(!is_null($endDate)){
            $tasks->where(DB::raw('DATE(tasks.`due_date`)'), '<=', $endDate);
        }

        if($projectId != 0){
            $tasks->where('tasks.project_id', '=', $projectId);
        }

        if($employeeId != 0){
            $tasks->where('tasks.user_id', $employeeId);
        }

        $tasks->get();

        return Datatables::of($tasks)
            ->editColumn('due_date', function($row){
                if($row->due_date->isPast()) {
                    return '<span class="text-danger">'.$row->due_date->format('d M, y').'</span>';
                }
                return '<span class="text-success">'.$row->due_date->format('d M, y').'</span>';
            })
            ->editColumn('name', function($row){
                return ($row->image) ? '<img src="'.asset('user-uploads/avatar/'.$row->image).'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name) : '<img src="'.asset('default-profile-2.png').'"
                                                            alt="user" class="img-circle" width="30"> '.ucwords($row->name);
            })
            ->editColumn('heading', function($row){
                return ucfirst($row->heading);
            })
            ->editColumn('status', function($row){
                if($row->status == 'incomplete'){
                    return '<label class="label label-danger">'.__('app.incomplete').'</label>';
                }
                return '<label class="label label-success">'.__('app.completed').'</label>';
            })
            ->editColumn('project_name', function ($row) {
                if(is_null($row->project_id)){
                    return "";
                }
                return '<a href="' . route('admin.projects.show', $row->project_id) . '">' . ucfirst($row->project_name) . '</a>';
            })
            ->rawColumns(['status', 'project_name', 'due_date', 'name'])
            ->removeColumn('project_id')
            ->removeColumn('image')
            ->make(true);
    }

}
