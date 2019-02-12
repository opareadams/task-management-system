<?php

namespace App\Http\Controllers\Member;

use App\Helper\Reply;
use App\Http\Requests\Notice\StoreNotice;
use App\ModuleSetting;
use App\Notice;
use App\Notifications\NewNotice;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use Yajra\Datatables\Facades\Datatables;

class MemberNoticesController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.noticeBoard');
        $this->pageIcon = 'ti-layout-media-overlay';

        if(!ModuleSetting::checkModule('notices')){
            abort(403);
        }
    }

    public function index() {
        if(!$this->user->can('view_notice')){
            abort(403);
        }
        $this->notices = Notice::orderBy('id', 'desc')->limit(10)->get();
        return view('member.notices.index', $this->data);
    }

    public function create()
    {
        if(!$this->user->can('add_notice')){
            abort(403);
        }
        return view('member.notices.create', $this->data);
    }

    public function store(StoreNotice $request)
    {
        $notice = new Notice();
        $notice->heading = $request->heading;
        $notice->description = $request->description;
        $notice->save();

        $users = User::allEmployees();

        Notification::send($users, new NewNotice($notice));

        $this->logSearchEntry($notice->id, 'Notice: '.$notice->heading, 'admin.notices.edit');

        return Reply::redirect(route('member.notices.index'), __('messages.noticeAdded'));
    }

    public function edit($id)
    {
        if(!$this->user->can('edit_notice')){
            abort(403);
        }
        $this->notice = Notice::findOrFail($id);
        return view('member.notices.edit', $this->data);
    }

    public function update(StoreNotice $request, $id)
    {
        $notice = Notice::findOrFail($id);
        $notice->heading = $request->heading;
        $notice->description = $request->description;
        $notice->save();

        Notification::send(User::allEmployees(), new NewNotice($notice));

        return Reply::success(__('messages.noticeUpdated'));
    }

    public function destroy($id)
    {
        Notice::destroy($id);
        return Reply::success(__('messages.noticeDeleted'));
    }

    public function data()
    {
        $users = Notice::all();

        return Datatables::of($users)
            ->addColumn('action', function($row){
                $action = '';

                if($this->user->can('edit_notice')){
                    $action.= '<a href="'.route('member.notices.edit', [$row->id]).'" class="btn btn-info btn-circle"
                      data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>';
                }

                if($this->user->can('delete_notice')) {
                    $action .= ' <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-user-id="' . $row->id . '" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
                }
                return $action;
            })
            ->editColumn(
                'created_at',
                function ($row) {
                    return Carbon::parse($row->created_at)->format('d F, Y');
                }
            )
            ->make(true);
    }
}
