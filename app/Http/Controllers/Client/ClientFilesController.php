<?php

namespace App\Http\Controllers\Client;

use App\Helper\Reply;
use App\ModuleSetting;
use App\Project;
use App\ProjectFile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ClientFilesController extends ClientBaseController
{

    public function __construct() {
        parent::__construct();
        $this->pageTitle = __('app.menu.projects');
        $this->pageIcon = 'icon-layers';

        if(!ModuleSetting::checkModule('projects')){
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = new ProjectFile();
            $file->user_id = $this->user->id;
            $file->project_id = $request->project_id;

            $request->file->store('user-uploads/project-files/'.$request->project_id);
            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $request->file->hashName();

            $file->size = $request->file->getSize();
            $file->save();
            $this->logProjectActivity($request->project_id, ucwords($this->user->name).__('messages.clientUploadedAFileToTheProject'));
        }

        $this->project = Project::findOrFail($request->project_id);
        $view = view('client.project-files.ajax-list', $this->data)->render();
        return Reply::successWithData(__('messages.fileUploadedSuccessfully'), ['html' => $view]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->project = Project::findOrFail($id);
        if($this->project->checkProjectClient()){
            return view('client.project-files.show', $this->data);
        }
        else{
            return redirect(route('client.dashboard.index'));
        }
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
        //
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

    public function download($id) {
        $file = ProjectFile::findOrFail($id);
        return response()->download('user-uploads/project-files/'.$file->project_id.'/'.$file->hashname);
    }
}
