@extends('layouts.member-app')

@section('page-title')
    <div class="row bg-title">
        <!-- .page title -->
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title"><i class="{{ $pageIcon }}"></i> {{ $pageTitle }}</h4>
        </div>
        <!-- /.page title -->
        <!-- .breadcrumb -->
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="{{ route('member.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
    <style>
        .col-in {
            padding: 0 20px !important;

        }

        .fc-event{
            font-size: 10px !important;
        }

    </style>
@endpush

@section('content')


    <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">Your Speed (Tasks Completed)</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                            <div id='myChart'></div>
                           <!-- <div id="myDiv"></div>-->
                            
                    </div>
                </div>
            </div>
        </div>  

    <!-- <div id="myDiv" style="width: 180px; height: 100px;"> Plotly chart will be drawn inside this DIV --</div>-->
        

    <div class="row"></div>
    <div class="row">
        @if(\App\ModuleSetting::checkModule('projects'))
        <div class="col-md-3 col-sm-6">
            <div class="white-box">
                <div class="col-in row">
                    <h3 class="box-title">@lang('modules.dashboard.totalProjects')</h3>
                    <ul class="list-inline two-part">
                        <li><i class="icon-layers text-info"></i></li>
                        <li class="text-right"><span class="counter">{{ $totalProjects }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if(\App\ModuleSetting::checkModule('timelogs'))
        <div class="col-md-3 col-sm-6">
            <div class="white-box" style="padding-bottom: 32px">
                <div class="col-in row">
                    <h3 class="box-title">@lang('modules.dashboard.totalHoursLogged')</h3>
                    <ul class="list-inline two-part">
                        <li><i class="icon-clock text-warning"></i></li>
                        <li class="text-right">{{ $counts->totalHoursLogged }}</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if(\App\ModuleSetting::checkModule('tasks'))
        
        <div class="col-md-3 col-sm-6">
            <div class="white-box">
                <div class="col-in row">
                    <h3 class="box-title">@lang('modules.dashboard.totalPendingTasks')</h3>
                    <ul class="list-inline two-part">
                        <li><i class="ti-alert text-danger"></i></li>
                        <li class="text-right"><span class="counter">{{ $counts->totalPendingTasks }}</span></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">
            <div class="white-box">
                <div class="col-in row">
                    <h3 class="box-title">@lang('modules.dashboard.totalCompletedTasks')</h3>
                    <ul class="list-inline two-part">
                        <li><i class="ti-check-box text-success"></i></li>
                        <li class="text-right"><span class="counter">{{ $counts->totalCompletedTasks }}</span></li>
                    </ul>
                </div>
            </div>
        </div>
        @endif

    </div>
    <!-- .row -->

    <div class="row">

        @if(\App\ModuleSetting::checkModule('attendance'))
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('app.menu.attendance')</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <div class="col-xs-6">
                            <h3>@lang('modules.attendance.clock_in')</h3>
                        </div>
                        <div class="col-xs-6">
                            <h3>@lang('modules.attendance.clock_in') IP</h3>
                        </div>
                        <div class="col-xs-6">
                            @if(is_null($todayAttendance))
                                {{ \Carbon\Carbon::now()->timezone($global->timezone)->format('h:i A') }}
                            @else
                                {{ $todayAttendance->clock_in_time->timezone($global->timezone)->format('h:i A') }}
                            @endif
                        </div>
                        <div class="col-xs-6">
                            {{ $todayAttendance->clock_in_ip or request()->ip() }}
                        </div>

                        @if(!is_null($todayAttendance) && !is_null($todayAttendance->clock_out_time))
                            <div class="col-xs-6 m-t-20">
                                <label for="">@lang('modules.attendance.clock_out')</label>
                                <br>{{ $todayAttendance->clock_out_time->timezone($global->timezone)->format('h:i A') }}
                            </div>
                            <div class="col-xs-6 m-t-20">
                                <label for="">@lang('modules.attendance.clock_out') IP</label>
                                <br>{{ $todayAttendance->clock_out_ip }}
                            </div>
                        @endif

                        <div class="col-xs-12 m-t-20">
                            <label for="">@lang('modules.attendance.working_from')</label>
                            @if(is_null($todayAttendance))
                                <input type="text" class="form-control" id="working_from" name="working_from">
                            @else
                                <br> {{ $todayAttendance->working_from }}
                            @endif
                        </div>

                        <div class="col-xs-6 m-t-20">
                            @if(is_null($todayAttendance))
                                <button class="btn btn-success btn-sm" id="clock-in">@lang('modules.attendance.clock_in')</button>
                            @endif
                            @if(!is_null($todayAttendance) && is_null($todayAttendance->clock_out_time))
                                <button class="btn btn-danger btn-sm" id="clock-out">@lang('modules.attendance.clock_out')</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(\App\ModuleSetting::checkModule('tasks'))
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('modules.dashboard.overdueTasks')</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <ul class="list-task list-group" data-role="tasklist">
                            <li class="list-group-item" data-role="task">
                                <strong>@lang('app.title')</strong> <span
                                        class="pull-right"><strong>@lang('app.dueDate')</strong></span>
                            </li>
                            @forelse($pendingTasks as $key=>$task)
                                <li class="list-group-item" data-role="task">
                                    {{ ($key+1).'. '.ucfirst($task->heading) }}
                                    @if(!is_null($task->project_id))
                                        <a href="{{ route('member.projects.show', $task->project_id) }}" class="text-danger">{{ ucwords($task->project->project_name) }}</a>
                                    @endif
                                    <label class="label label-danger pull-right">{{ $task->due_date->format('d M') }}</label>
                                </li>
                            @empty
                                <li class="list-group-item" data-role="task">
                                    @lang('messages.noOpenTasks')
                                </li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>

    <div class="row" >

        @if(\App\ModuleSetting::checkModule('projects'))
        <div class="col-md-6" id="project-timeline">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('modules.dashboard.projectActivityTimeline')</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <div class="steamline">
                            @foreach($projectActivities as $activity)
                                <div class="sl-item">
                                    <div class="sl-left"><i class="fa fa-circle text-info"></i>
                                    </div>
                                    <div class="sl-right">
                                        <div><h6><a href="{{ route('member.projects.show', $activity->project_id) }}" class="text-danger">{{ ucwords($activity->project_name) }}:</a> {{ $activity->activity }}</h6> <span class="sl-date">{{ $activity->created_at->timezone($global->timezone)->diffForHumans() }}</span></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(\App\ModuleSetting::checkModule('employees'))
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">@lang('modules.dashboard.userActivityTimeline')</div>
                <div class="panel-wrapper collapse in">
                    <div class="panel-body">
                        <div class="steamline">
                            @forelse($userActivities as $key=>$activity)
                                <div class="sl-item">
                                    <div class="sl-left">
                                        {!!  ($activity->user->image) ? '<img src="'.asset('user-uploads/avatar/'.$activity->user->image).'"
                                                                    alt="user" class="img-circle">' : '<img src="'.asset('default-profile-2.png').'"
                                                                    alt="user" class="img-circle">' !!}
                                    </div>
                                    <div class="sl-right">
                                        <div class="m-l-40"><a href="{{ route('member.employees.show', $activity->user_id) }}" class="text-success">{{ ucwords($activity->user->name) }}</a> <span  class="sl-date">{{ $activity->created_at->timezone($global->timezone)->diffForHumans() }}</span>
                                            <p>{!! ucfirst($activity->activity) !!}</p>
                                        </div>
                                    </div>
                                </div>
                                @if(count($userActivities) > ($key+1))
                                    <hr>
                                @endif
                            @empty
                                <div>@lang('messages.noActivityByThisUser')</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif



    </div>

@endsection

@push('footer-script')
<script>
    $('#clock-in').click(function () {
        var workingFrom = $('#working_from').val();

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: '{{route('member.attendances.store')}}',
            type: "POST",
            data: {
                working_from: workingFrom,
                _token: token
            },
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    })

    @if(!is_null($todayAttendance))
    $('#clock-out').click(function () {

        var token = "{{ csrf_token() }}";

        $.easyAjax({
            url: '{{route('member.attendances.update', $todayAttendance->id)}}',
            type: "PUT",
            data: {
                _token: token
            },
            success: function (response) {
                if(response.status == 'success'){
                    window.location.reload();
                }
            }
        })
    })
    @endif

</script>
<script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
<script>
  /*  // Enter a speed between 0 and 180
var completedTask = {{ $counts->totalCompletedTasks }};
var pendingTasks = {{ $counts->totalPendingTasks }};
var totalTasks = completedTask+pendingTasks;
var speed = (completedTask/totalTasks)*180;
//console.log('Speed is '+speed);
var level = speed;

// Trig to calc meter point
var degrees = 180 - level,
     radius = .5;
var radians = degrees * Math.PI / 180;
var x = radius * Math.cos(radians);
var y = radius * Math.sin(radians);

// Path: may have to change to create a better triangle
    var mainPath = 'M -.0 -0.025 L .0 0.025 L ',
     pathX = String(x),
     space = ' ',
     pathY = String(y),
     pathEnd = ' Z';
var path = mainPath.concat(pathX,space,pathY,pathEnd);

var data = [{ type: 'scatter',
   x: [0], y:[0],
    marker: {size: 28, color:'850000'},
    showlegend: false,
    name: 'speed',
    text: level,
    hoverinfo: 'text+name'},
  { values: [50/6, 50/6, 50/6, 50/6, 50/6, 50/6, 50],
  rotation: 90,
  text: ['VERY FAST!!!', 'Pretty Fast', 'Fast', 'Average',
            'Slow', 'Super Slow', ''],
  textinfo: 'text',
  textposition:'inside',      
  marker: {colors:['rgba(14, 127, 0, .5)', 'rgba(110, 154, 22, .5)',
                         'rgba(170, 202, 42, .5)', 'rgba(202, 209, 95, .5)',
                         'rgba(210, 206, 145, .5)', 'rgba(232, 226, 202, .5)',
                         'rgba(255, 255, 255, 0)']},
  labels: ['151-180', '121-150', '91-120', '61-90', '31-60', '0-30', ''],
  hoverinfo: 'label',
  hole: .5,
  type: 'pie',
  showlegend: false
}];

var layout = {
  shapes:[{
      type: 'path',
      path: path,
      fillcolor: '850000',
      line: {
        color: '850000'
      }
    }],
  title: '<b>Your Productivity/ Speed</b> <br> Range: 0-180 <br> <b> '+level+' </b>Completions per Total Tasks',
  //height: 370,
  width: 310,
  xaxis: {zeroline:false, showticklabels:false,
             showgrid: false, range: [-1, 1]},
  yaxis: {zeroline:false, showticklabels:false,
             showgrid: false, range: [-1, 1]}
};

Plotly.newPlot('myDiv', data, layout,{responsive: true,displayModeBar: false});*/
</script>
<script>
    var completedTask = {{ $counts->totalCompletedTasks }};
    var pendingTasks = {{ $counts->totalPendingTasks }};
    var totalTasks = completedTask+pendingTasks;
  //  var speed = (completedTask/totalTasks)*100;
    var speed = completedTask;
    //console.log('Speed is '+speed);
   

        var myConfig2 = {
          "type": "gauge",
          "title":{
            "text":speed + " out of "+totalTasks+ ""
            },
          "scale-r": {
            "aperture": 200, //Scale Range
            //"values": "0:100:20" //and minimum, maximum, and step scale values.
            "values": "0:"+totalTasks+":1" //and minimum, maximum, and step scale values.
          },
          "series": [{
            "values": [speed]
          }]
        };
    
        zingchart.render({
          id: 'myChart',
          data: myConfig2,
         // height: "50%",
         // width: "50%"
        });
      </script>
        
@endpush