<?php

namespace App\Http\Controllers\Member;

use App\Attendance;
use App\Issue;
use App\ModuleSetting;
use App\Project;
use App\ProjectActivity;
use App\ProjectTimeLog;
use App\Task;
use App\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class MemberDashboardController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();

        $this->pageTitle = __('app.menu.dashboard');
        $this->pageIcon = 'icon-speedometer';
    }

    public function index() {
        $this->totalProjects = Project::select('projects.id')
            ->join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', '=', $this->user->id)->count();

        $this->counts = DB::table('users')
            ->select(
                DB::raw('(select IFNULL(sum(project_time_logs.total_minutes),0) from `project_time_logs` where user_id = '.$this->user->id.') as totalHoursLogged '),
                DB::raw('(select count(tasks.id) from `tasks` where status="completed" and user_id = '.$this->user->id.') as totalCompletedTasks'),
                DB::raw('(select count(tasks.id) from `tasks` where status="incomplete" and user_id = '.$this->user->id.') as totalPendingTasks')
            )
            ->first();

        $timeLog = intdiv($this->counts->totalHoursLogged, 60).' hrs ';

        if(($this->counts->totalHoursLogged % 60) > 0){
            $timeLog.= ($this->counts->totalHoursLogged % 60).' mins';
        }

        $this->counts->totalHoursLogged = $timeLog;

        $this->projectActivities = ProjectActivity::join('projects', 'projects.id', '=', 'project_activity.project_id')
            ->join('project_members', 'project_members.project_id', '=', 'projects.id')
            ->where('project_members.user_id', '=', $this->user->id)
            ->select('projects.project_name', 'project_activity.created_at', 'project_activity.activity', 'project_activity.project_id')
            ->limit(15)->orderBy('project_activity.id', 'desc')->get();

        $this->userActivities = UserActivity::limit(15)->orderBy('id', 'desc')->where('user_id', $this->user->id)->get();

        $this->pendingTasks = Task::where('status', 'incomplete')
            ->where(DB::raw('DATE(due_date)'), '<=', Carbon::today()->format('Y-m-d'))
            ->where('user_id', $this->user->id)
            ->get();
        $this->todayAttendance = Attendance::where(DB::raw('DATE(clock_in_time)'), Carbon::today()->format('Y-m-d'))
            ->where('user_id', $this->user->id)->first();

        return view('member.dashboard.index', $this->data);
    }
}
