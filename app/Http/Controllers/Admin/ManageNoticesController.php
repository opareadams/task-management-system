<?php

namespace App\Http\Controllers\Admin;

use App\Helper\Reply;
use App\Http\Requests\Notice\StoreNotice;
use App\ModuleSetting;
use App\Notice;
use App\Notifications\NewNotice;
use App\Team;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use Yajra\Datatables\Facades\Datatables;
use GuzzleHttp\Client;

class ManageNoticesController extends AdminBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.noticeBoard');
        $this->pageIcon = 'ti-layout-media-overlay';

        if(!ModuleSetting::checkModule('notices')){
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
        return view('admin.notices.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->teams = Team::all();
        return view('admin.notices.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNotice $request)
    {
        $notice = new Notice();
        $notice->heading = $request->heading;
        $notice->description = $request->description;
        $notice->save();

        if($request->team_id != ''){
            $users = User::teamUsers($request->team_id);
        }
        else{
            $users = User::allEmployees();
        }

        Notification::send($users, new NewNotice($notice));

        foreach($users as $user){

                /***************               push notification to mobile app   ***********/
                $client = new Client([
                    'base_uri' => 'https://fcm.googleapis.com/fcm/send',
                ]);
                //$payload = file_get_contents('/my-data.xml');
                $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                    //'debug' => TRUE,
                    'body' => json_encode(
                        [
                            'to' => ''.$user->firebase_token.'',
                            'collapse_key' => 'type_a',
                            'notification' => 
                                [
                                    'body' => ''.$notice->heading.'',
                                    'title' => 'New Notice Alert'
                                ]
                        ]),
                    
                    'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'key=AIzaSyDmlO8naLCTyaUgMAI--wHfuFsFUCuQj5c',
                    ]
                ]);
            }
        /************** *****************************/

        $this->logSearchEntry($notice->id, 'Notice: '.$notice->heading, 'admin.notices.edit');

        return Reply::redirect(route('admin.notices.index'), __('messages.noticeAdded'));
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
        $this->notice = Notice::findOrFail($id);
        return view('admin.notices.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreNotice $request, $id)
    {
        $notice = Notice::findOrFail($id);
        $notice->heading = $request->heading;
        $notice->description = $request->description;
        $notice->save();

        $users = User::allEmployees();

        Notification::send(User::allEmployees(), new NewNotice($notice));

        foreach($users as $user){

            /***************               push notification to mobile app   ***********/
            $client = new Client([
                'base_uri' => 'https://fcm.googleapis.com/fcm/send',
            ]);
            //$payload = file_get_contents('/my-data.xml');
            $response = $client->post('https://fcm.googleapis.com/fcm/send', [
                //'debug' => TRUE,
                'body' => json_encode(
                    [
                        'to' => ''.$user->firebase_token.'',
                        'collapse_key' => 'type_a',
                        'notification' => 
                            [
                                'body' => ''.$notice->heading.'',
                                'title' => 'Update on Notice Alert'
                            ]
                    ]),
                
                'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'key=AIzaSyDmlO8naLCTyaUgMAI--wHfuFsFUCuQj5c',
                ]
            ]);
        }
    /************** *****************************/

        return Reply::success(__('messages.noticeUpdated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
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
                return '<a href="'.route('admin.notices.edit', [$row->id]).'" class="btn btn-info btn-circle"
                      data-toggle="tooltip" data-original-title="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>

                      <a href="javascript:;" class="btn btn-danger btn-circle sa-params"
                      data-toggle="tooltip" data-user-id="'.$row->id.'" data-original-title="Delete"><i class="fa fa-times" aria-hidden="true"></i></a>';
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
