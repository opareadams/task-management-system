<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Project;
use App\ProjectTimeLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class TimeLogReportController extends AdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.timeLogReport');
        $this->pageIcon = 'ti-pie-chart';
    }

    public function index() {
        $this->projects = Project::all();
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();

        $this->chartData = DB::table('project_time_logs')
            ->where('start_time', '>=', $this->fromDate)
            ->where('start_time', '<=', $this->toDate)
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get([
                DB::raw('DATE_FORMAT(start_time,\'%d/%M/%y\') as date'),
                DB::raw('FLOOR(sum(total_minutes/60)) as total_hours')
            ])
        ->toJSON();

        return view('admin.reports.time-log.index', $this->data);
    }

    public function store(Request $request) {
        $fromDate = $request->startDate;
        $toDate = $request->endDate;
        $projectId = $request->projectId;

        $timeLog = ProjectTimeLog::select('start_time', DB::raw('DATE_FORMAT(start_time,\'%d/%M/%y\') as date'),DB::raw('FLOOR(sum(total_minutes/60)) as total_hours'))
            ->where('start_time', '>=', $fromDate)
            ->where('start_time', '<=', $toDate);

        if(!is_null($projectId)){
            $timeLog->where('project_id', '=', $projectId);
        }

        $timeLog = $timeLog->groupBy('date')
            ->orderBy('start_time', 'ASC')
            ->get()
            ->toJson();

        if(empty($timeLog)){
            return Reply::error('No record found.');
        }
        return Reply::successWithData(__('messages.reportGenerated'), ['chartData' => $timeLog]);
    }
}
