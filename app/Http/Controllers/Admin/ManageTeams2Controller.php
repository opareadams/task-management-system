<?php

namespace App\Http\Controllers\Admin;

use App\EmployeeTeam2;
use App\Helper\Reply;
use App\Http\Requests\Team2\StoreTeam;
use App\ModuleSetting;
use App\Team2;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ManageTeams2Controller extends AdminBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.teams2');
        $this->pageIcon = 'icon-user';

        if(!ModuleSetting::checkModule('employees')){
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
        $this->groups = Team2::all();
        return view('admin.teams2.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.teams2.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeam $request)
    {
        $group = new Team2();
        $group->team2_name = $request->team2_name;
        $group->save();

        return Reply::redirect(route('admin.teams2.index'), 'Group created successfully.');
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
        $this->group = Team2::findOrFail($id);
        $this->employees = User::doesntHave('group2', 'and', function($query) use ($id){
            $query->where('team2_id', $id);
        })
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->where('roles.name', '<>', 'client')
            ->groupBy('users.id')
            ->get();
        return view('admin.teams2.edit', $this->data);
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
        $group = Team2::find($id);
        $group->team2_name = $request->team2_name;
        $group->save();

        if(!empty($users = $request->user_id)){
            foreach($users as $user){
                $member = new EmployeeTeam2();
                $member->user_id = $user;
                $member->team2_id = $id;
                $member->save();
            }
        }


        return Reply::redirect(route('admin.teams2.index'), __('messages.groupUpdatedSuccessfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Team2::destroy($id);
        return Reply::dataOnly(['status' => 'success']);
    }
}
