<?php

namespace App\Http\Controllers\Admin;

use App\Leave;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\Datatables\Facades\Datatables;

class LeaveReportController extends AdminBaseController
{
    /**
     * LeaveReportController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leaveReport');
        $this->pageIcon = 'ti-pie-chart';
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->employees = User::allEmployees();
        $this->fromDate = Carbon::today()->subDays(30);
        $this->toDate = Carbon::today();

        return view('admin.reports.leave.index', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show($id)
    {
        $this->modalHeader = 'approved';
        $this->casualLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Casual')
            ->where('leaves.status', 'approved')
            ->where('leaves.user_id', $id)
            ->count();
        $this->sickLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Sick')
            ->where('leaves.status', 'approved')
            ->where('leaves.user_id', $id)
            ->count();
        $this->earnedLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Earned')
            ->where('leaves.status', 'approved')
            ->where('leaves.user_id', $id)
            ->count();
        $this->leaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->select('leave_types.type_name', 'leaves.leave_date', 'leaves.reason')
            ->where('leaves.status', 'approved')
            ->where('leaves.user_id', $id)
            ->get();

        return view('admin.reports.leave.leave-detail', $this->data);
    }

    /**
     * @param null $startDate
     * @param null $endDate
     * @param null $employeeId
     * @return mixed
     */
    public function data($startDate = null, $endDate = null, $employeeId = null)
    {
        $leavesList = Leave::join('users', 'users.id', '=', 'leaves.user_id')
            ->join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->selectRaw(
                'users.id, users.name, leaves.leave_date, 
                sum( if(leaves.status = \'approved\', 1, 0)) as count_approved_leaves,
                sum( if(leaves.status = \'pending\', 1, 0)) as count_pending_leaves, 
                sum( if(leaves.leave_date > '.Carbon::now()->format('Y-m-d').' and (leaves.status != \'rejected\'), 1, 0)) as count_upcoming_leaves, 
                leave_types.type_name'
            );

        if(!is_null($startDate)){
            $leavesList->where(DB::raw('DATE(leaves.`leave_date`)'), '>=', $startDate);
        }

        if(!is_null($endDate)){
            $leavesList->where(DB::raw('DATE(leaves.`leave_date`)'), '<=', $endDate);
        }

        if($employeeId != 0) {
            $leavesList->where('leaves.user_id', $employeeId);
        }

        $leaves = $leavesList
            ->groupBy('users.id')
            ->get();



        return Datatables::of($leaves)
            ->addColumn('employee', function($row) {
                return ucwords($row->name);
            })
            ->addColumn('approve', function($row) {
                return '<div class="label-success label">'.$row->count_approved_leaves.'</div>
                <a href="javascript:;" class="view-approve" data-pk="'.$row->id.'">View</a>';
            })
            ->addColumn('pending', function($row) {
                return '<div class="label-warning label">'.$row->count_pending_leaves.'</div>
                <a href="javascript:;" data-pk="'.$row->id.'" class="view-pending">View</a>';
            })
            ->addColumn('upcoming', function($row) {
                return '<div class="label-info label">'.$row->count_upcoming_leaves.'</div>
                <a href="javascript:;" data-pk="'.$row->id.'" class="view-upcoming">View</a>';
            })
            ->addColumn('action', function($row) {
                return '<a href="' . route('admin.leave-report.export', [$row->id]) . '" class="btn btn-info btn-sm"
                      data-toggle="tooltip" data-original-title="Export to excel"><i class="ti-export" aria-hidden="true"></i> Export</a>';
            })
            ->addIndexColumn()
            ->rawColumns(['approve', 'upcoming', 'pending', 'action'])
            ->make(true);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function pendingLeaves($id)
    {
        $this->modalHeader = 'pending';
        $this->casualLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Casual')
            ->where('leaves.status', 'pending')
            ->where('leaves.user_id', $id)
            ->count();
        $this->sickLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Sick')
            ->where('leaves.status', 'pending')
            ->where('leaves.user_id', $id)
            ->count();
        $this->earnedLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Earned')
            ->where('leaves.status', 'pending')
            ->where('leaves.user_id', $id)
            ->count();
        $this->leaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->select('leave_types.type_name', 'leaves.leave_date', 'leaves.reason')
            ->where('leaves.status', 'pending')
            ->where('leaves.user_id', $id)
            ->get();

        return view('admin.reports.leave.leave-detail', $this->data);
    }

    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function upcomingLeaves($id)
    {
        $this->modalHeader = 'upcoming';
        $this->casualLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Casual')
            ->where('leaves.status', 'pending')
            ->orWhere('leaves.status', 'approved')
            ->where('leaves.leave_date','>', Carbon::now()->format('Y-m-d'))
            ->where('leaves.user_id', $id)
            ->count();
        $this->sickLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Sick')
            ->where('leaves.status', 'pending')
            ->orWhere('leaves.status', 'approved')
            ->where('leaves.leave_date','>', Carbon::now()->format('Y-m-d'))
            ->where('leaves.user_id', $id)
            ->count();
        $this->earnedLeaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leave_types.type_name', 'Earned')
            ->where('leaves.status', 'pending')
            ->orWhere('leaves.status', 'approved')
            ->where('leaves.leave_date','>', Carbon::now()->format('Y-m-d'))
            ->where('leaves.user_id', $id)
            ->count();
        $this->leaves = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->select('leave_types.type_name', 'leaves.leave_date', 'leaves.reason')
            ->where('leaves.status', 'pending')
            ->orWhere('leaves.status', 'approved')
            ->where('leaves.leave_date','>', Carbon::now()->format('Y-m-d'))
            ->where('leaves.user_id', $id)
            ->get();

        return view('admin.reports.leave.leave-detail', $this->data);
    }

    public function export($id) {
        $employees = User::find($id);
        $rows = Leave::join('leave_types', 'leave_types.id', '=', 'leaves.leave_type_id')
            ->where('leaves.user_id', $id)
            ->select(
                'leave_types.type_name',
                'leaves.leave_date',
                'leaves.reason',
                'leaves.status',
                'leaves.reject_reason'
            )
            ->get();

        // Initialize the array which will be passed into the Excel
        // generator.
        $exportArray = [];

        // Define the Excel spreadsheet headers
        $exportArray[] = ['Leave Type', 'Date', 'Reason', 'Status', 'Reject Reason'];

        // Convert each member of the returned collection into an array,
        // and append it to the payments array.
        foreach ($rows as $row) {
            $exportArray[] = $row->toArray();
        }

        // Generate and return the spreadsheet
        Excel::create($employees->name.' Leaves', function ($excel) use ($employees, $exportArray) {

            // Set the spreadsheet title, creator, and description
            $excel->setTitle($employees->name.' Leaves');
            $excel->setCreator('Worksuite')->setCompany($this->companyName);
            $excel->setDescription('Leaves file');

            // Build the spreadsheet, passing in the payments array
            $excel->sheet('sheet1', function ($sheet) use ($exportArray) {
                $sheet->fromArray($exportArray, null, 'A1', false, false);

                $sheet->row(1, function ($row) {

                    // call row manipulation methods
                    $row->setFont(array(
                        'bold' => true
                    ));

                });

            });


        })->download('xlsx');
    }
}
