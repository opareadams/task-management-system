<?php

namespace App\Http\Controllers\Member;

use App\EmployeeDetails;
use App\Helper\Reply;
use App\Http\Requests\ProjectMembers\StoreProjectMembers;
use App\Http\Requests\User\UpdateProfile;
use App\Issue;
use App\ModuleSetting;
use App\Project;
use App\ProjectActivity;
use App\ProjectFile;
use App\ProjectMember;
use App\ProjectTimeLog;
use App\StorageSetting;
use App\Task;
use App\User;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\Datatables\Facades\Datatables;

/**
 * Class MemberProjectsController
 * @package App\Http\Controllers\Member
 */
class MemberProjectFilesController extends MemberBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageIcon = 'icon-layers';
        $this->pageTitle = __('app.menu.projects');
        if(config('filesystems.default') == 's3') {
            $this->url = "https://".config('filesystems.disks.s3.bucket').".s3.amazonaws.com/";
        }

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
            $storage = config('filesystems.default');
            $file = new ProjectFile();
            $file->user_id = $this->user->id;
            $file->project_id = $request->project_id;
            switch($storage) {
                case 'local':
                    $request->file->storeAs('user-uploads/project-files/'.$request->project_id, $request->file->getClientOriginalName());
                    break;
                case 's3':
                    Storage::disk('s3')->putFileAs('project-files/'.$request->project_id, $request->file, $request->file->getClientOriginalName(), 'public');
                    break;
                case 'google':
                    $dir = '/';
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                    $dir = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', 'project-files')
                        ->first();

                    if(!$dir) {
                        Storage::cloud()->makeDirectory('project-files');
                    }

                    $directory = $dir['path'];
                    $recursive = false;
                    $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                    $directory = $contents->where('type', '=', 'dir')
                        ->where('filename', '=', $request->project_id)
                        ->first();

                    if ( ! $directory) {
                        Storage::cloud()->makeDirectory($dir['path'].'/'.$request->project_id);
                        $contents = collect(Storage::cloud()->listContents($directory, $recursive));
                        $directory = $contents->where('type', '=', 'dir')
                            ->where('filename', '=', $request->project_id)
                            ->first();
                    }

                    Storage::cloud()->putFileAs($directory['basename'], $request->file, $request->file->getClientOriginalName());

                    $file->google_url = Storage::cloud()->url($directory['path'].'/'.$request->file->getClientOriginalName());

                    break;
                case 'dropbox':
                    Storage::disk('dropbox')->putFileAs('project-files/'.$request->project_id.'/', $request->file, $request->file->getClientOriginalName());
                    $dropbox = new Client(['headers' => ['Authorization' => "Bearer ".config('filesystems.disks.dropbox.token'), "Content-Type" => "application/json"]]);
                    $res = $dropbox->request('POST', 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings',
                        [\GuzzleHttp\RequestOptions::JSON => ["path" => '/project-files/'.$request->project_id.'/'.$request->file->getClientOriginalName()]]
                    );
                    $dropboxResult = $res->getBody();
                    $dropboxResult = json_decode($dropboxResult, true);
                    $file->dropbox_link = $dropboxResult['url'];
                    break;
            }

            $file->filename = $request->file->getClientOriginalName();
            $file->hashname = $request->file->hashName();
            $file->size = $request->file->getSize();
            $file->save();
            $this->logProjectActivity($request->project_id, __('messages.newFileUploadedToTheProject'));
        }

        $this->project = Project::findOrFail($request->project_id);
        $view = view('member.project-files.ajax-list', $this->data)->render();
        return Reply::successWithData(__('messages.fileUploaded'), ['html' => $view]);
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
        return view('member.project-files.show', $this->data);
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
        $storage = config('filesystems.default');
        $file = ProjectFile::findOrFail($id);
        switch($storage) {
            case 'local':
                File::delete('user-uploads/project-files/'.$file->project_id.'/'.$file->filename);
                break;
            case 's3':
                Storage::disk('s3')->delete('project-files/'.$file->project_id.'/'.$file->filename);
                break;
            case 'google':
                Storage::disk('google')->delete('project-files/'.$file->project_id.'/'.$file->filename);
                break;
            case 'dropbox':
                Storage::disk('dropbox')->delete('project-files/'.$file->project_id.'/'.$file->filename);
                break;
        }
        ProjectFile::destroy($id);
        $this->project = Project::findOrFail($file->project_id);
        $view = view('member.project-files.ajax-list', $this->data)->render();
        return Reply::successWithData(__('messages.fileDeleted'), ['html' => $view]);
    }


    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function download($id) {
        $storage = config('filesystems.default');
        $file = ProjectFile::findOrFail($id);
        switch($storage) {
            case 'local':
                return response()->download('user-uploads/project-files/'.$file->project_id.'/'.$file->filename);
                break;
            case 's3':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('project-files/'.$file->project_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
            case 'google':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $dir = '/';
                $recursive = false; // Get subdirectories also?
                $contents = collect(Storage::cloud()->listContents($dir, $recursive));
                $directory = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', 'project-files')
                    ->first();

                $direct = $directory['path'];
                $recursive = false;
                $contents = collect(Storage::cloud()->listContents($direct, $recursive));
                $directo = $contents->where('type', '=', 'dir')
                    ->where('filename', '=', $file->project_id)
                    ->first();

                $readStream = Storage::cloud()->getDriver()->readStream($directo['path']);
                return response()->stream(function () use ($readStream) {
                    fpassthru($readStream);
                }, 200, [
                    'Content-Type' => $ext,
                    'Content-disposition' => 'attachment; filename="'.$file->filename.'"',
                ]);
                break;
            case 'dropbox':
                $ext = pathinfo($file->filename, PATHINFO_EXTENSION);
                $fs = Storage::getDriver();
                $stream = $fs->readStream('project-files/'.$file->project_id.'/'.$file->filename);
                return Response::stream(function() use($stream) {
                    fpassthru($stream);
                }, 200, [
                    "Content-Type" => $ext,
                    "Content-Length" => $file->size,
                    "Content-disposition" => "attachment; filename=\"" .basename($file->filename) . "\"",
                ]);
                break;
        }
    }

}
