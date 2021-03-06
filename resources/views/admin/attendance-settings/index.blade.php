@extends('layouts.app')

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
                <li><a href="{{ route('admin.dashboard') }}">@lang('app.menu.home')</a></li>
                <li class="active">{{ $pageTitle }}</li>
            </ol>
        </div>
        <!-- /.breadcrumb -->
    </div>
@endsection

@push('head-script')
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css') }}">
<link rel="stylesheet" href="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.css') }}">
@endpush

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-inverse">
                <div class="panel-heading">@lang('app.update') @lang('app.menu.attendanceSettings')</div>

                <div class="vtabs customvtab m-t-10">

                    @include('sections.admin_setting_menu')

                    <div class="tab-content">
                        <div id="vhome3" class="tab-pane active">
                            {!! Form::open(['id'=>'editSettings','class'=>'ajax-form','method'=>'PUT']) !!}
                            <div class="row">
                                <div class="form-body ">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="input-group bootstrap-timepicker timepicker">
                                                <label>@lang('modules.attendance.officeStartTime')</label>
                                                <input type="text" name="office_start_time" id="office_start_time"
                                                       class="form-control"
                                                       value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSetting->office_start_time)->format('h:i A') }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <div class="input-group bootstrap-timepicker timepicker">
                                                <label>@lang('modules.attendance.officeEndTime')</label>
                                                <input type="text" name="office_end_time" id="office_end_time"
                                                       class="form-control"
                                                       value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $attendanceSetting->office_end_time)->format('h:i A') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="late_mark_duration">@lang('modules.attendance.lateMark')</label>
                                            <input type="number" class="form-control" id="late_mark_duration"
                                                   name="late_mark_duration"
                                                   value="{{ $attendanceSetting->late_mark_duration }}">
                                        </div>
                                    </div>

                                    <div class="col-xs-12">
                                        <div class="form-group">
                                            <div class="checkbox checkbox-info  col-md-10">
                                                <input id="employee_clock_in_out" name="employee_clock_in_out" value="yes"
                                                       @if($attendanceSetting->employee_clock_in_out == "yes") checked
                                                       @endif
                                                       type="checkbox">
                                                <label for="employee_clock_in_out">@lang('modules.attendance.allowSelfClock')</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-xs-12">
                                        <hr>
                                        <label class="control-label col-md-12 p-l-0">@lang('modules.attendance.officeOpenDays')</label>
                                        <div class="form-group">
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_mon" name="office_open_days[]" value="1"
                                                       @foreach($openDays as $day)
                                                           @if($day == 1) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_mon">@lang('app.monday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_tues" name="office_open_days[]" value="2"
                                                       @foreach($openDays as $day)
                                                       @if($day == 2) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_tues">@lang('app.tuesday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_wed" name="office_open_days[]" value="3"
                                                       @foreach($openDays as $day)
                                                       @if($day == 3) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_wed">@lang('app.wednesday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_thurs" name="office_open_days[]" value="4"
                                                       @foreach($openDays as $day)
                                                       @if($day == 4) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_thurs">@lang('app.thursday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_fri" name="office_open_days[]" value="5"
                                                       @foreach($openDays as $day)
                                                       @if($day == 5) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_fri">@lang('app.friday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2 m-l-0">
                                                <input id="open_sat" name="office_open_days[]" value="6"
                                                       @foreach($openDays as $day)
                                                       @if($day == 6) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_sat">@lang('app.saturday')</label>
                                            </div>
                                            <div class="checkbox checkbox-inline checkbox-info  col-md-2">
                                                <input id="open_sun" name="office_open_days[]" value="0"
                                                       @foreach($openDays as $day)
                                                       @if($day == 0) checked @endif
                                                       @endforeach
                                                       type="checkbox">
                                                <label for="open_sun">@lang('app.sunday')</label>
                                            </div>
                                        </div>
                                    </div>

                                <div class="col-md-12">
                                    <div class="form-actions m-t-15">
                                        <button type="submit" id="save-form"
                                                class="btn btn-success waves-effect waves-light m-r-10">
                                            @lang('app.update')
                                        </button>
                                        <button type="reset"
                                                class="btn btn-inverse waves-effect waves-light">@lang('app.reset')</button>
                                    </div>

                                </div>

                                </div>



                            </div>
                            {!! Form::close() !!}

                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>


    </div>
    <!-- .row -->

@endsection

@push('footer-script')
<script src="{{ asset('plugins/bower_components/bootstrap-select/bootstrap-select.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('plugins/bower_components/timepicker/bootstrap-timepicker.min.js') }}"></script>


<script>

    $('#office_end_time, #office_start_time').timepicker();

    $('#save-form').click(function () {
        $.easyAjax({
            url: '{{route('admin.attendance-settings.update', ['1'])}}',
            container: '#editSettings',
            type: "POST",
            redirect: true,
            data: $('#editSettings').serialize()
        })
    });


</script>

@endpush

