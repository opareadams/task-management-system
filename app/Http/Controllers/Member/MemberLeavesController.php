<?php

namespace App\Http\Controllers\Member;

use App\Helper\Reply;
use App\Http\Requests\Leaves\StoreLeave;
use App\Http\Requests\Leaves\UpdateLeave;
use App\Leave;
use App\LeaveType;
use App\ModuleSetting;
use App\Notifications\LeaveApplication;
use App\Notifications\NewLeaveRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MemberLeavesController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.leaves');
        $this->pageIcon = 'icon-logout';

        if(!ModuleSetting::checkModule('leaves')){
            abort(403);
        }
    }

    public function index()
    {
        $this->leaves = Leave::byUser($this->user->id);
        $this->leaveTypes = LeaveType::byUser($this->user->id);
        $this->allowedLeaves = LeaveType::sum('no_of_leaves');
        $this->pendingLeaves = Leave::where('status', 'pending')
            ->where('user_id', $this->user->id)
            ->orderBy('leave_date', 'asc')
            ->get();

        return view('member.leaves.index', $this->data);
    }

    public function create()
    {
        $this->leaveTypes = LeaveType::all();
        return view('member.leaves.create', $this->data);
    }

    public function store(StoreLeave $request)
    {
        if($request->duration == 'multiple'){
            $dates = explode(',', $request->multi_date);
            foreach($dates as $date){
                $leave = new Leave();
                $leave->user_id = $request->user_id;
                $leave->leave_type_id = $request->leave_type_id;
                $leave->duration = $request->duration;
                $leave->leave_date = Carbon::parse($date)->format('Y-m-d');
                $leave->reason = $request->reason;
                $leave->status = $request->status;
                $leave->save();
            }
        }
        else{
            $leave = new Leave();
            $leave->user_id = $request->user_id;
            $leave->leave_type_id = $request->leave_type_id;
            $leave->duration = $request->duration;
            $leave->leave_date = Carbon::parse($request->leave_date)->format('Y-m-d');
            $leave->reason = $request->reason;
            $leave->status = $request->status;
            $leave->save();
        }

        //      Send notification to user
        $notifyUsers = User::allAdmins();
        foreach ($notifyUsers as $notifyUser) {
            $notifyUser->notify(new NewLeaveRequest($leave));
        }

        $notifyLeavesUser = User::find($request->user_id);
        $notifyLeavesUser->notify(new LeaveApplication($leave));

        return Reply::redirect(route('member.leaves.index'), __('messages.leaveAssignSuccess'));
    }

    public function show($id)
    {
        $this->leave = Leave::findOrFail($id);
        return view('member.leaves.show', $this->data);
    }

    public function edit($id)
    {
        $this->leaveTypes = LeaveType::all();
        $this->leave = Leave::findOrFail($id);
        $view = view('member.leaves.edit', $this->data)->render();
        return Reply::dataOnly(['status' => 'success', 'view' => $view]);
    }

    public function update(UpdateLeave $request, $id)
    {
        $leave = Leave::findOrFail($id);
        $leave->user_id = $request->user_id;
        $leave->leave_type_id = $request->leave_type_id;
        $leave->leave_date = Carbon::parse($request->leave_date)->format('Y-m-d');
        $leave->reason = $request->reason;
        $leave->status = $request->status;
        $leave->save();

        return Reply::redirect(route('member.leaves.index'), __('messages.leaveAssignSuccess'));
    }

    public function destroy($id)
    {
        Leave::destroy($id);
        return Reply::success('messages.leaveDeleteSuccess');
    }

    public function leaveAction(Request $request){
        Leave::destroy($request->leaveId);

        return Reply::success(__('messages.leaveStatusUpdate'));
    }

}
