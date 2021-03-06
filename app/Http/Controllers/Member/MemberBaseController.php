<?php

namespace App\Http\Controllers\Member;

use App\EmailNotificationSetting;
use App\ModuleSetting;
use App\Notification;
use App\ProjectActivity;
use App\ProjectTimeLog;
use App\Setting;
use App\StickyNote;
use App\Traits\FileSystemSettingTrait;
use App\UniversalSearch;
use App\UserActivity;
use App\UserChat;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use App\ThemeSetting;

class MemberBaseController extends Controller
{
    use FileSystemSettingTrait;
    /**
     * @var array
     */
    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[ $name ]);
    }

    /**
     * UserBaseController constructor.
     */
    public function __construct()
    {
        // Inject currently logged in user object into every view of user dashboard

        $this->global = Setting::first();
        $this->emailSetting = EmailNotificationSetting::all();
        $this->companyName = $this->global->company_name;
        $this->employeeTheme = ThemeSetting::where('panel', 'employee')->first();

        App::setLocale($this->global->locale);
        Carbon::setUtf8(true);
        Carbon::setLocale($this->global->locale);
        setlocale(LC_TIME,$this->global->locale.'_'.strtoupper($this->global->locale));
        $this->setFileSystemConfigs();

        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            $this->notifications = $this->user->notifications;
            $this->timer = ProjectTimeLog::memberActiveTimer($this->user->id);
            $this->unreadMessageCount = UserChat::where('to', $this->user->id)->where('message_seen', 'no')->count();
            $this->unreadExpenseCount = Notification::where('notifiable_id', $this->user->id)
                ->where(function($query){
                    $query->where('type', 'App\Notifications\NewExpenseStatus');
                    $query->orWhere('type', 'App\Notifications\NewExpenseMember');
                })
                ->whereNull('read_at')
                ->count();
            $this->unreadProjectCount = Notification::where('notifiable_id', $this->user->id)
                ->where('type', 'App\Notifications\NewProjectMember')
                ->whereNull('read_at')
                ->count();
            $this->stickyNotes = StickyNote::where('user_id', $this->user->id)->orderBy('updated_at', 'desc')->get();
            return $next($request);
        });


    }

    public function logProjectActivity($projectId, $text) {
        $activity = new ProjectActivity();
        $activity->project_id = $projectId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logUserActivity($userId, $text) {
        $activity = new UserActivity();
        $activity->user_id = $userId;
        $activity->activity = $text;
        $activity->save();
    }

    public function logSearchEntry($searchableId, $title, $route) {
        $search = new UniversalSearch();
        $search->searchable_id = $searchableId;
        $search->title = $title;
        $search->route_name = $route;
        $search->save();
    }

}
